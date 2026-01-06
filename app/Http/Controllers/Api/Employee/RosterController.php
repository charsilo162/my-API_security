<?php
namespace App\Http\Controllers\Api\Employee;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Http\Resources\ServiceRequestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RosterController extends Controller
{
public function index(Request $request)
{
    $employee = $request->user()->employee; // Get the employee profile of the logged-in user

    $roster = ServiceRequest::whereHas('assignedStaff', function($q) use ($employee) {
        $q->where('employees.id', $employee->id);
    })
    ->with(['client']) // So they know which company they are working for
    ->orderBy('start_date', 'asc')
    ->paginate($request->per_page ?? 10);
// dd($roster);
    return ServiceRequestResource::collection($roster);
}
        public function confirm($uuid)
{
    $employee = Auth::user()->employee;
    
    // Find the specific mission
    $request = ServiceRequest::where('uuid', $uuid)->firstOrFail();

    // Update the pivot table record for this specific guard
    $request->assignedStaff()->updateExistingPivot($employee->id, [
        'confirmed_at' => now(),
    ]);

    return response()->json([
        'message' => 'Attendance confirmed successfully.',
        'confirmed_at' => now()->toDateTimeString()
    ]);
}

}