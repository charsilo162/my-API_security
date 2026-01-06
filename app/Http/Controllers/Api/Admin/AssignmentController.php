<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Http\Resources\ServiceRequestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentController extends Controller
{
    // List all requests from all companies for the Admin Dashboard
   // ServiceRequestController.php

    public function index(Request $request)
        {
            $query = ServiceRequest::with(['client', 'assignedStaff.user']);

            // Filter by Status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // NEW: Search Logic
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhereHas('client', function($clientQ) use ($search) {
                        $clientQ->where('company_name', 'LIKE', "%{$search}%");
                    });
                });
            }

            return ServiceRequestResource::collection(
                $query->latest()->paginate($request->input('per_page', 15))
            );
        }

    // Update Request Status (Lifecycle Management)
    public function updateStatus(Request $request, $uuid)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,assigned,active,completed,cancelled',
            'remarks' => 'nullable|string'
        ]);

        $serviceRequest = ServiceRequest::where('uuid', $uuid)->firstOrFail();

        $serviceRequest->update([
            'status' => $request->status,
            'admin_remarks' => $request->remarks
        ]);

        return response()->json([
            'message' => "Request status updated to {$request->status}",
            'data' => new ServiceRequestResource($serviceRequest)
        ]);
    }
    public function show($uuid)
        {
            // Fetch request with client and already assigned staff
            $serviceRequest = ServiceRequest::with(['client', 'assignedStaff.user'])
                ->where('uuid', $uuid)
                ->firstOrFail();

            return new ServiceRequestResource($serviceRequest);
        }

    // Note: The assignStaff() method we wrote earlier also lives in this class.

   public function assignStaff(Request $request, $uuid)
        {
            $request->validate([
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
                'remarks' => 'nullable|string'
            ]);


            $serviceRequest = ServiceRequest::where('uuid', $uuid)->firstOrFail();

            // 1. Availability Check (Perform this BEFORE the transaction)
            foreach ($request->employee_ids as $id) {
                $isBusy = DB::table('service_request_assignments')
                    ->join('service_requests', 'service_requests.id', '=', 'service_request_assignments.service_request_id')
                    ->where('employee_id', $id)
                    // Ensure we don't count the CURRENT request if we are re-assigning
                    ->where('service_requests.id', '!=', $serviceRequest->id) 
                    ->where(function($query) use ($serviceRequest) {
                        $query->whereBetween('start_date', [$serviceRequest->start_date, $serviceRequest->end_date])
                            ->orWhereBetween('end_date', [$serviceRequest->start_date, $serviceRequest->end_date]);
                    })->exists();

                if ($isBusy) {
                    return response()->json([
                        'message' => "Employee ID {$id} is already assigned to another task during this period."
                    ], 422);
                }
            }

            // 2. Database Update
            DB::transaction(function () use ($request, $serviceRequest) {
                // Sync prevents duplicate entries in the pivot table
                $serviceRequest->assignedStaff()->sync($request->employee_ids);

                $serviceRequest->update([
                    'status' => 'assigned',
                    'admin_remarks' => $request->remarks
                ]);
            });

            return response()->json([
                'message' => 'Personnel assigned successfully',
                'data' => new ServiceRequestResource($serviceRequest->load('assignedStaff'))
            ]);
        }



}