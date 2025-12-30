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
    $this->authorize('viewAny', Employee::class);

    // Eager load the user relationship
    $query = Employee::with('user');

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            // Search employee table fields (like designation or department if needed)
            $q->where('designation', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%")
              
              // Search linked User table fields
              ->orWhereHas('user', function($userQuery) use ($search) {
                  $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            // Optional: Allows searching for "First Last" combined
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
              });
        });
    }

    return EmployeeResource::collection(
        $query->latest()->paginate($request->get('per_page', 10))
    );
}
    public function show(Employee $employee)
        {
            $this->authorize('viewAny', $employee);

            return new EmployeeResource($employee->load('user'));
        }

   public function store(Request $request)
    {
        $this->authorize('create', Employee::class);

        $data = $request->validate([
            // User/Bio Data
            'first_name'          => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'nullable|string|min:8',
            'phone'               => 'nullable|string',
            'address'             => 'nullable|string',
            'gender'              => 'nullable|in:male,female,other',
            'bio'                 => 'nullable|string',
            'date_of_birth'       => 'nullable|date',
            'photo'               => 'nullable|image|max:2048', // 2MB Max
            
            // Employee/Work Data
            'designation'         => 'required|string',
            'department'          => 'required|string',
            'joining_date'        => 'required|date',

            // Banking Information
            'account_holder_name' => 'nullable|string',
            'account_number'      => 'nullable|string',
            'bank_name'           => 'nullable|string',
            'branch_name'         => 'nullable|string',
            'routing_number'      => 'nullable|string',
            'swift_code'          => 'nullable|string',
        ]);

        $employee = DB::transaction(function () use ($request, $data) {
            // 1. Handle Photo Upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('profile_photos', 'public');
            }

            // 2. Create User Record
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
                'type'          => 'user',
            ]);

            // 3. Create Employee Record (Work & Banking)
            return $user->employee()->create([
                'designation'         => $data['designation'],
                'department'          => $data['department'],
                'joining_date'        => $data['joining_date'],
                'account_holder_name' => $data['account_holder_name'],
                'account_number'      => $data['account_number'],
                'bank_name'           => $data['bank_name'],
                'branch_name'         => $data['branch_name'],
                'routing_number'      => $data['routing_number'],
                'swift_code'          => $data['swift_code'],
            ]);
        });

        return response()->json([
            'message' => 'Employee created successfully',
            'data'    => new EmployeeResource($employee->load('user')),
        ], 201);
    }

public function update(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);
    
         Log::info('Files in request:', $request->allFiles());
        // Validation
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email,' . $employee->user_id,
            'photo'      => 'nullable|image|max:2048',
            'password'   => 'nullable|min:8',
            'address'=> 'nullable|string',
            'gender'        => 'nullable|in:male,female,other',
            'bio'           => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            
            // Employee fields
            'designation'         => 'required|string',
            'department'          => 'required|string',
            'joining_date'        => 'required|date',
            
            // Bank info fields
            'account_holder_name' => 'nullable|string',
            'account_number'      => 'nullable|string',
            'bank_name'           => 'nullable|string',
            'branch_name'         => 'nullable|string',
            'routing_number'      => 'nullable|string',
            'swift_code'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $employee, $data) {
            // 1. Update User Data
            $userUpdate = [
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'phone'         => $request->phone,
                'address'       => $request->address,
                'gender'        => $request->gender,
                'bio'           => $request->bio,
                'date_of_birth' => $request->date_of_birth,
            ];

            if ($request->filled('password')) {
                $userUpdate['password'] = bcrypt($request->password);
            }

            // 2. Handle Image Upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('profile_photos', 'public');
                $userUpdate['photo_path'] = $path;
            }

            $employee->user->update($userUpdate);

            // 3. Update Employee Data (Only fields in employees table)
            $employee->update($request->only([
                'designation', 'department', 'joining_date',
                'account_holder_name', 'account_number', 'bank_name',
                'branch_name', 'routing_number', 'swift_code'
            ]));
        });

        return response()->json(['message' => 'Employee updated successfully']);
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