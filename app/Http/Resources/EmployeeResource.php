<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // We use $this->user to access the related bio data from the users table
        return [
            'id'            => $this->id,
            'employee_id'   => $this->id, // Usually the primary key or a code
            'uuid'   => $this->uuid ,// Usually the primary key or a code
            
            // Personal Data (from Users table)
            'first_name'    => $this->user->first_name ?? null,
            'bio'    => $this->user->bio ?? null,
            'last_name'     => $this->user->last_name ?? null,
            'full_name'     => trim(($this->user->first_name ?? '') . ' ' . ($this->user->last_name ?? '')),
            'email'         => $this->user->email ?? null,
            'phone'         => $this->user->phone ?? null,
            'address'       => $this->user->address ?? null,
            // 'photo'         => $this->user->photo_path ?? null,
             'photo' =>  $this->user->photo_path 
            ? asset('storage/' .  $this->user->photo_path)
            : asset('storage/profile_photos/img.jpg'),
            'date_of_birth' => $this->user->date_of_birth ?? null,
            'gender'        => $this->user->gender ?? null,
            
            // Job Details (from Employees table)
            'designation'   => $this->designation,
            'employee_code'   => $this->employee_code,
            'department'    => $this->department,
            'joining_date'  => $this->joining_date ? $this->joining_date->format('Y-m-d') : null,

            // Banking Information (from Employees table)
            'banking' => [
                'account_holder_name' => $this->account_holder_name,
                'account_number'      => $this->account_number,
                'bank_name'           => $this->bank_name,
                'branch_name'         => $this->branch_name,
                'routing_number'      => $this->routing_number,
                'swift_code'          => $this->swift_code,
            ],

            // Full User Relationship (Optional/Conditional)
            'user' => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'type'  => $this->user->type,
                'bio'   => $this->user->bio,
            ]),
            
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}