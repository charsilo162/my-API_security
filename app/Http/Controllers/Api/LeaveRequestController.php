<?php
namespace App\Http\Controllers\Api;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    // --- FOR ADMIN: List all requests ---
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'leaveType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return LeaveRequestResource::collection($query->latest()->paginate(10));
    }

    // --- FOR EMPLOYEE: Submit new request ---
    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1; // Basic calculation

        // Check if employee has enough balance
        $balance = LeaveBalance::where('user_id', auth()->id())
            ->where('leave_type_id', $data['leave_type_id'])
            ->first();

        if (!$balance || $balance->remaining_days < $days) {
            return response()->json(['message' => 'Insufficient leave balance'], 422);
        }

        $leave = LeaveRequest::create([
            'user_id'       => auth()->id(),
            'leave_type_id' => $data['leave_type_id'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'total_days'    => $days,
            'reason'        => $data['reason'],
            'status'        => 'pending',
        ]);

        return new LeaveRequestResource($leave);
    }

    // --- FOR ADMIN: Update Status (The missing Figma link) ---
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 422);
        }

        DB::transaction(function () use ($request, $leaveRequest) {
            $leaveRequest->update([
                'status' => $request->status,
                'manager_remarks' => $request->remarks,
                'approved_by' => auth()->id()
            ]);

            // If approved, deduct from balance
            if ($request->status === 'approved') {
                $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->first();

                if ($balance) {
                    $balance->decrement('remaining_days', $leaveRequest->total_days);
                    $balance->increment('used_days', $leaveRequest->total_days);
                }
            }
        });

        return response()->json(['message' => "Leave request {$request->status} successfully"]);
    }



    public function myHistory()
{
    $leaves = LeaveRequest::with('leaveType')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return LeaveRequestResource::collection($leaves);
}

public function myBalances()
{
    $balances = \App\Models\LeaveBalance::with('leaveType')
        ->where('user_id', auth()->id())
        ->where('year', date('Y'))
        ->get();

    return $balances->map(function ($balance) {
        return [
            'leave_type_id' => $balance->leave_type_id,
            'leave_type_name' => $balance->leaveType->name,
            'entitled_days' => $balance->entitled_days,
            'remaining_days' => $balance->remaining_days,
            'used_days' => $balance->used_days,
        ];
    });
}
}