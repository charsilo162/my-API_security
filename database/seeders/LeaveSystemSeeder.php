<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Database\Seeder;

class LeaveSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Leave Types
        $types = [
            [
                'name' => 'Annual Leave',
                'default_days' => 20,
                'color_code' => '#10b981', // Green
            ],
            [
                'name' => 'Sick Leave',
                'default_days' => 12,
                'color_code' => '#ef4444', // Red
            ],
            [
                'name' => 'Casual Leave',
                'default_days' => 10,
                'color_code' => '#f59e0b', // Amber
            ],
            [
                'name' => 'Maternity Leave',
                'default_days' => 90,
                'color_code' => '#8b5cf6', // Purple
            ],
        ];

        foreach ($types as $type) {
            $leaveType = LeaveType::updateOrCreate(['name' => $type['name']], $type);

            // 2. Assign these balances to all existing users
            User::all()->each(function ($user) use ($leaveType) {
                LeaveBalance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => date('Y'),
                    ],
                    [
                        'entitled_days' => $leaveType->default_days,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->default_days,
                    ]
                );
            });
        }
    }
}
