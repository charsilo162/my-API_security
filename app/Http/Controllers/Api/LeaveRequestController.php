<?php
namespace App\Http\Controllers\Api;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
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
    public function show(LeaveRequest $leaveRequest)
        {
            // Because we added getRouteKeyName() => 'uuid' in the Model, 
            // Laravel automatically finds the record by UUID.
            
            return new LeaveRequestResource($leaveRequest->load(['user', 'leaveType']));
        }
        public function getTypes()
        {
            $types = LeaveType::where('is_active', true)->get();
            
            // Using the resource collection automatically wraps it in 'data'
            return LeaveTypeResource::collection($types);
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

            // 1. Get the Leave Type to know the "Default" days allowed
            $leaveType = \App\Models\LeaveType::findOrFail($data['leave_type_id']);

            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);
            $days = $start->diffInDays($end) + 1;

            // 2. FIND OR CREATE: If they don't have a balance for 2026, create it now
            $balance = \App\Models\LeaveBalance::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'leave_type_id' => $data['leave_type_id'],
                    'year' => date('Y'), // Logic tied to the current year
                ],
                [
                    'entitled_days' => $leaveType->default_days, // e.g., 20
                    'used_days' => 0,
                    'remaining_days' => $leaveType->default_days, // e.g., 20
                ]
            );

            // 3. Now check if they have enough
            if ($balance->remaining_days < $days) {
                return response()->json(['message' => "Insufficient balance. You only have {$balance->remaining_days} days left."], 422);
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
                    'status' => 'required|in:approved,rejected,pending',
                    'remarks' => 'nullable|string'
                ]);

                $oldStatus = $leaveRequest->status;
                $newStatus = $request->status;

                // Use a try-catch block within the transaction for better error reporting
                try {
                    return DB::transaction(function () use ($request, $leaveRequest, $oldStatus, $newStatus) {
                        
                        $leaveYear = \Carbon\Carbon::parse($leaveRequest->start_date)->year;
                        
                        // Fetch the balance once to use for both logic blocks
                        $balance = \App\Models\LeaveBalance::where('user_id', $leaveRequest->user_id)
                            ->where('leave_type_id', $leaveRequest->leave_type_id)
                            ->where('year', $leaveYear)
                            ->first();

                        // 1. REFUND LOGIC: Moving FROM 'approved' TO something else
                        if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                            if ($balance) {
                                $balance->increment('remaining_days', $leaveRequest->total_days);
                                $balance->decrement('used_days', $leaveRequest->total_days);
                            }
                        }

                        // 2. DEDUCTION LOGIC: Moving TO 'approved' (and weren't already)
                        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                            if (!$balance) {
                                throw new \Exception("No leave balance record found for this user for the year {$leaveYear}.");
                            }

                            if ($balance->remaining_days < $leaveRequest->total_days) {
                                throw new \Exception("Insufficient leave balance. User has {$balance->remaining_days} days left, but requested {$leaveRequest->total_days}.");
                            }

                            // Deduct the days
                            $balance->decrement('remaining_days', $leaveRequest->total_days);
                            $balance->increment('used_days', $leaveRequest->total_days);
                        }

                        // 3. Update the Record
                        $leaveRequest->update([
                            'status' => $newStatus,
                            'manager_remarks' => $request->remarks,
                            'approved_by' => auth()->id()
                        ]);

                        return response()->json(['message' => "Status updated to {$newStatus} successfully."]);
                    });
                } catch (\Exception $e) {
                    // Return the error message to the frontend (Livewire will catch this as an error)
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => ['status' => [$e->getMessage()]]
                    ], 422);
                }
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

    // App/Http/Controllers/Api/LeaveRequestController.php

    public function destroy(LeaveRequest $leaveRequest)
    {
        // 1. Policy check: Only the owner can delete
        if ($leaveRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. State check: Only 'pending' requests can be deleted
        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be cancelled.'], 422);
        }

        $leaveRequest->delete();

        return response()->json(['message' => 'Leave request cancelled successfully.']);
    }
}