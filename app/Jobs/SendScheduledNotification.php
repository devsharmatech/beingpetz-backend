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

            if ($audienceType === 'all') {
                $receivers = User::whereNotNull('device_token')->pluck('device_token')->toArray();
                Log::info('All users receivers:', ['count' => count($receivers)]);

            } elseif ($audienceType === 'custom') {
                $locations = $this->notification->audience['locations'] ?? [];
                Log::info('Custom audience locations:', ['locations' => $locations]);
                
                $receivers = User::whereIn('location', $locations)
                    ->whereNotNull('device_token')
                    ->pluck('device_token')
                    ->toArray();
                Log::info('Custom receivers:', ['count' => count($receivers)]);
            } else {
                // Single receiver
                $receiver = User::find($this->notification->user_id);
                $receivers = $receiver && $receiver->device_token ? [$receiver->device_token] : [];
                Log::info('Single receiver:', ['token' => $receivers[0] ?? 'None']);
            }

            if (empty($receivers)) {
                Log::warning('No receivers found for notification');
                $this->notification->update([
                    'is_sent' => true,
                    'status'  => false,
                ]);
                return;
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($receivers as $deviceToken) {
                $result = $this->sendFCMNotification(
                    $deviceToken,
                    $this->notification->title,
                    $this->notification->message,
                    [
                        'sender_id'     => (string) $this->notification->sender_id,
                        'type'          => $this->notification->type,
                        'notifiable_id' => (string) $this->notification->notifiable_id,
                    ]
                );
                
                if ($result) {
                    $successCount++;
                    Log::info('FCM sent successfully to:', ['token' => substr($deviceToken, 0, 10) . '...']);
                } else {
                    $failedCount++;
                    Log::error('FCM failed for token:', ['token' => substr($deviceToken, 0, 10) . '...']);
                }
            }

            Log::info('FCM Summary:', [
                'success' => $successCount,
                'failed' => $failedCount,
                'total' => count($receivers)
            ]);

            // Update notification status
            $this->notification->update([
                'is_sent' => true,
                'status'  => $successCount > 0,
                'sent_at' => now(),
            ]);

            Log::info('Notification processing completed');

        } catch (\Exception $e) {
            Log::error('Scheduled notification job failed: ' . $e->getMessage());
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);

            $this->notification->update([
                'status'  => false,
                'is_sent' => false,
            ]);
        }
    }

    private function sendFCMNotification($deviceToken, $title, $body, $data = [])
    {
        $serverKey = env('FIREBASE_SERVER_KEY');

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to'           => $deviceToken,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
            ],
            'data' => $data,
        ]);

        return $response->successful();
    }
}