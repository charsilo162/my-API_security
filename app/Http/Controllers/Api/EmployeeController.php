<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
        {
            $query = Employee::with('user');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('designation', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                    });
                });
            }

            return EmployeeResource::collection(
                $query->latest()->paginate($request->get('per_page', 10))
            );
        }
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'nullable|string|min:8',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
            'gender'        => 'nullable|in:male,female,other',
            'bio'           => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'photo'         => 'nullable|image|max:2048',
            'designation'   => 'required|string',
            'department'    => 'required|string',
            'joining_date'  => 'required|date',
            // Banking...
            'account_holder_name' => 'nullable|string',
            'account_number'      => 'nullable|string',
            'bank_name'           => 'nullable|string',
        ]);

        $employee = DB::transaction(function () use ($request, $data) {
            $photoPath = $request->hasFile('photo') 
                ? $request->file('photo')->store('profile_photos', 'public') 
                : null;

            $user = User::create([
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'password'      => bcrypt($data['password'] ?? 'password123'),
                'phone'         => $data['phone'],
                'address'       => $data['address'],
                'gender'        => $data['gender'],
                'bio'           => $data['bio'],
                'date_of_birth' => $data['date_of_birth'],
                'photo_path'    => $photoPath,
                'type'          => 'employee', // Explicitly set type for staff
            ]);

            return $user->employee()->create([
               
                'designation'   => $data['designation'],
                'department'    => $data['department'],
                'joining_date'  => $data['joining_date'],
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'account_number'      => $data['account_number'] ?? null,
                'bank_name'           => $data['bank_name'] ?? null,
                // Add other banking fields if present in data...
            ]);
        });

        return response()->json([
            'message' => 'Security personnel registered successfully',
            'data'    => new EmployeeResource($employee->load('user')),
        ], 201);
    }


        public function update(Request $request, Employee $employee)
        {
            $this->authorize('update', $employee);

            // 1. Validation (Matches your balanced migrations)
            $data = $request->validate([
                'first_name'    => 'required|string',
                'last_name'     => 'required|string',
                'email'         => 'required|email|unique:users,email,' . $employee->user_id,
                'phone'         => 'nullable|string',
                'address'       => 'nullable|string',
                'gender'        => 'nullable|in:male,female,other',
                'bio'           => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'photo'         => 'nullable|image|max:2048',
                'password'      => 'nullable|min:8',

                // Employee fields
                'designation'   => 'required|string',
                'department'    => 'required|string',
                'joining_date'  => 'required|date',
                
                // Bank info
                'account_holder_name' => 'nullable|string',
                'account_number'      => 'nullable|string',
                'bank_name'           => 'nullable|string',
                'branch_name'         => 'nullable|string',
                'routing_number'      => 'nullable|string',
                'swift_code'          => 'nullable|string',
            ]);

            DB::transaction(function () use ($request, $employee, $data) {
                
                // 2. Prepare User Data (Only columns that exist in the 'users' table)
                $userData = [
                    'first_name'    => $data['first_name'],
                    'last_name'     => $data['last_name'],
                    'email'         => $data['email'],
                    'phone'         => $data['phone'],
                    'address'       => $data['address'],
                    'gender'        => $data['gender'],
                    'bio'           => $data['bio'],
                    'date_of_birth' => $data['date_of_birth'],
                ];

                if ($request->filled('password')) {
                    $userData['password'] = bcrypt($data['password']);
                }

                if ($request->hasFile('photo')) {
                    // Delete old photo if exists logic can go here
                    $userData['photo_path'] = $request->file('photo')->store('profile_photos', 'public');
                }

                // UPDATE USER TABLE
                $employee->user->update($userData);

                // 3. Prepare Employee Data (Only columns that exist in the 'employees' table)
                $employeeData = [
                    'designation'         => $data['designation'],
                    'department'          => $data['department'],
                    'joining_date'        => $data['joining_date'],
                    'account_holder_name' => $data['account_holder_name'],
                    'account_number'      => $data['account_number'],
                    'bank_name'           => $data['bank_name'],
                    'branch_name'         => $data['branch_name'],
                    'routing_number'      => $data['routing_number'],
                    'swift_code'          => $data['swift_code'],
                ];

                // UPDATE EMPLOYEE TABLE
                $employee->update($employeeData);
            });

            return response()->json([
                'message' => 'Employee updated successfully',
                'data'    => new EmployeeResource($employee->load('user'))
            ]);
        }
    
      public function show(Employee $employee)
        {
            $this->authorize('viewAny', $employee);

            return new EmployeeResource($employee->load('user'));
        }

    public function destroy(Employee $employee)
{
    $this->authorize('delete', $employee);

    DB::transaction(function () use ($employee) {
        // This will also delete the employee due to 'cascade' in migration
        // or you can delete the user explicitly:
        $employee->user->delete(); 
    });

    return response()->json(['message' => 'Employee deleted successfully']);
}
   
}