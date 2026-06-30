<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\ContestEntry;

class ContestEntryController extends Controller
{
    // LIST + FILTER
    public function index(Request $request)
    {
        $query = ContestEntry::with(['user','contest']);

        if ($request->contest_id) {
            $query->where('contest_id', $request->contest_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $data = $query->latest()->paginate(10);

        return response()->json([
            'success'=>true,
            'message'=>'Entries list',
            'data'=>$data
        ]);
    }

    // APPROVE / REJECT
    public function updateStatus(Request $request, $id)
    {
        $entry = ContestEntry::findOrFail($id);

        $entry->update([
            'status' => $request->status // approved / rejected
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Status updated'
        ]);
    }

    // MARK WINNER
    public function markWinner($id)
    {
        $entry = ContestEntry::findOrFail($id);

        // optional: reset previous winners
        ContestEntry::where('contest_id',$entry->contest_id)
            ->update(['is_winner'=>0]);

        $entry->update([
            'is_winner'=>1
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Winner selected'
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        ContestEntry::findOrFail($id)->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Entry deleted'
        ]);
    }
}