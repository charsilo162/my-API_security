<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
public function toArray(Request $request): array
{
    return [
        'uuid' => $this->uuid,
        'employee' => [
            'full_name' => $this->user->first_name . ' ' . $this->user->last_name,
            'avatar' => $this->user->photo_path ? asset('storage/' . $this->user->photo_path) : null,
        ],
        'leave_type' => $this->leaveType->name,
        'start_date' => $this->start_date->format('d M Y'),
        'end_date' => $this->end_date->format('d M Y'),
        'days' => $this->total_days,
        'status' => $this->status, // pending, approved, rejected
        'reason' => $this->reason,
        'manager_remarks' => $this->manager_remarks,
        'created_at' => $this->created_at->diffForHumans(),
    ];
}

}