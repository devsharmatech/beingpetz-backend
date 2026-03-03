<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\User;
use App\Models\Test;


class SendScheduledNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function handle()
    {
        if ($this->notification->is_sent) {
            Log::info('Notification already sent, skipping');
            return;
        }

        try {
            Log::info('Processing notification:', ['id' => $this->notification->id]);
            
            $audienceType = $this->notification->audience['type'] ?? 'single';
            Log::info('Audience type:', ['type' => $audienceType]);

            $query = User::query()->whereNotNull('device_token');

            if ($audienceType === 'all') {
                // All users
            } elseif ($audienceType === 'custom') {
                $locations = $this->notification->audience['locations'] ?? [];
                if (!empty($locations)) {
                    $query->whereIn('location', $locations);
                }
            } else {
                // Single receiver
                $query->where('id', $this->notification->user_id);
            }

            $users = $query->get();
            
            if ($users->isEmpty()) {
                Log::warning('No receivers found for notification');
                $this->notification->update([
                    'is_sent' => true,
                    'status'  => false,
                ]);
                return;
            }

            $projectId = config('services.firebase.project_id');
            $credentialsPath = public_path(config('services.firebase.credentials_path'));
            $fcm = new \App\Services\FirebaseService($projectId, $credentialsPath);

            $successCount = 0;
            $failedCount = 0;

            foreach ($users as $user) {
                $result = $fcm->sendNotification(
                    [$user->device_token],
                    [
                        'title'          => $this->notification->title,
                        'body'           => $this->notification->message,
                        'sender_id'      => (string) $this->notification->sender_id,
                        'type'           => $this->notification->type,
                        'notification_id'=> (string) $this->notification->id,
                        'image'          => $this->notification->image ? asset('storage/' . $this->notification->image) : null,
                    ]
                );
                
                if ($result === true) {
                    $successCount++;
                } else {
                    $failedCount++;
                    Log::error('FCM failed for token:', ['error' => $result]);
                }
            }

            Log::info('FCM Summary:', [
                'success' => $successCount,
                'failed' => $failedCount,
                'total' => $users->count()
            ]);

            // Update notification status
            $this->notification->update([
                'is_sent' => true,
                'status'  => $successCount > 0,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Scheduled notification job failed: ' . $e->getMessage());
            $this->notification->update([
                'status'  => false,
                'is_sent' => false,
            ]);
        }
    }
}