<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServiceRequestResource extends JsonResource
{
public function toArray(Request $request): array
{
    $user = $request->user();
    
    // 1. Identify "My" specific assignment (if I am an employee)
    // We check the loaded relationship to avoid extra SQL queries
    $myAssignment = null;
    if ($user && $user->employee) {
        $myAssignment = $this->assignedStaff
            ->where('id', $user->employee->id)
            ->first();
    }

    return [
        'uuid'     => $this->uuid,
        'title'    => $this->title,
        'category' => ucfirst($this->category),
        'status'   => $this->status,
        
        /** * PERSONAL STATUS (For Employee Roster)
         * If the logged-in user is a guard, these fields tell them their own status.
         */
        'is_confirmed' => $myAssignment && $myAssignment->pivot->confirmed_at !== null,
        'confirmed_at' => ($myAssignment && $myAssignment->pivot->confirmed_at) 
            ? \Carbon\Carbon::parse($myAssignment->pivot->confirmed_at)->format('d M Y, H:i') 
            : null,
        
        'client_name' => $this->client->company_name ?? 'Private Client',
        'location'    => $this->client->address ?? 'Location in Briefing',
        
        'start_date' => $this->start_date->format('d M Y, H:i'),
        'end_date'   => $this->end_date->format('d M Y, H:i'),
        'briefing'   => $this->admin_remarks ?? 'No special instructions provided.',
        
        'required_staff_count' => $this->required_staff_count,

        /** * TEAM STATUS (For Admin Dashboard)
         * The Admin uses this collection. Each EmployeeResource within here 
         * MUST also handle the pivot->confirmed_at logic.
         */
        'assigned_personnel' => EmployeeResource::collection($this->whenLoaded('assignedStaff')),
        
        'created_at' => $this->created_at->diffForHumans(),
    ];
}
}