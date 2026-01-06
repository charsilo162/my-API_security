<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'employee_code' => $this->employee_code,
            
            // Personal Data from the linked User table
            'first_name'    => $this->user->first_name ?? null,
            'last_name'     => $this->user->last_name ?? null,
            'full_name'     => trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? '')),
            'email'         => $this->user->email ?? null,
            'phone'         => $this->user->phone ?? null,
            'address'       => $this->user->address ?? null,
            'bio'           => $this->user->bio ?? null,
            'gender'        => $this->user->gender ?? null,
            'date_of_birth' => $this->user->date_of_birth ?? null,
            'type'          => $this->user->type ?? 'employee',
            
            'photo' => $this->user->photo_path 
                ? asset('storage/' . $this->user->photo_path)
                : asset('storage/profile_photos/img.jpg'),
            
            // Job Details
            'designation'   => $this->designation,
            'department'    => $this->department,
            'joining_date'  => $this->joining_date ? $this->joining_date->format('Y-m-d') : null,

            // Banking Information
            'banking' => [
                'account_holder_name' => $this->account_holder_name,
                'account_number'      => $this->account_number,
                'bank_name'           => $this->bank_name,
                'branch_name'         => $this->branch_name,
                'routing_number'      => $this->routing_number,
                'swift_code'          => $this->swift_code,
            ],
            'is_confirmed' => $this->pivot && $this->pivot->confirmed_at !== null,
        'confirmed_at' => ($this->pivot && $this->pivot->confirmed_at) 
            ? \Carbon\Carbon::parse($this->pivot->confirmed_at)->format('d M, H:i') 
            : null,
  
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}