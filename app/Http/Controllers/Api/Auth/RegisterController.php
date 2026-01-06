<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
   
        $role = $request->input('role'); // 'employee' or 'client'

        if ($role === 'employee') {
            return $this->registerEmployee($request);
        }

        return $this->registerClient($request);
    }

     protected function registerEmployee(Request $request)
    {
        $data = $request->validate([
            // User Table Fields
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            
            // Employee Profile Fields
            'gender'        => 'nullable|string',
            'bio'           => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'designation'   => 'required|string',
            'department'    => 'required|string',
            'joining_date'  => 'required|date',
            
            // Banking Fields
            'account_holder_name' => 'nullable|string',
            'account_number'      => 'nullable|string',
            'bank_name'           => 'nullable|string',
            'branch_name'         => 'nullable|string',
            'routing_number'      => 'nullable|string',
            'swift_code'          => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $photoPath = $request->hasFile('photo') 
                ? $request->file('photo')->store('profile_photos', 'public') 
                : null;

            // 1. Create User
            $user = User::create([
                'uuid'          => (string) Str::uuid(),
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'email'         => $data['email'],
                'password'      => Hash::make($data['password']),
                'phone'         => $data['phone'],
                'address'       => $data['address'] ?? null,
                'photo_path'    => $photoPath,
                'type'          => 'employee',
            ]);

            // 2. Create Employee Profile
            $user->employee()->create([
                'uuid'                => (string) Str::uuid(),
                'gender'              => $data['gender'] ?? null,
                'bio'                 => $data['bio'] ?? null,
                'date_of_birth'       => $data['date_of_birth'] ?? null,
                'designation'         => $data['designation'],
                'department'          => $data['department'],
                'joining_date'        => $data['joining_date'],
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'account_number'      => $data['account_number'] ?? null,
                'bank_name'           => $data['bank_name'] ?? null,
                'branch_name'         => $data['branch_name'] ?? null,
                'routing_number'      => $data['routing_number'] ?? null,
                'swift_code'          => $data['swift_code'] ?? null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'Employee registered successfully',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user->load('employee')
            ], 201);
        });
    }

    protected function registerClient(Request $request)
    {
    //             \Log::info('===  REQUEST START ===', [
    

    //     'input'     => $request->all(),
    //     'files'     => $request->allFiles() ? array_keys($request->allFiles()) : [],
    // ]);
        $data = $request->validate([
            'first_name'   => 'required|string',
            'last_name'    => 'required|string',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:8',
            'phone'        => 'nullable|string',
            'address'      => 'nullable|string',
            'company_name' => 'required|string',
            'industry'     => 'nullable|string',
            'photo'        => 'nullable|image|max:2048',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $photoPath = $request->hasFile('photo') 
                ? $request->file('photo')->store('profile_photos', 'public') 
                : null;

            // 1. Create User
            $user = User::create([
                'uuid'       => (string) Str::uuid(),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'],
                'address'    => $data['address'] ?? null,
                'type'       => 'client',
                'photo_path' => $photoPath,
            ]);

            // 2. Create Client Profile
            $user->client()->create([
                'uuid'         => (string) Str::uuid(),
                'company_name' => $data['company_name'],
                'address' => $data['address'],
                'industry'     => $data['industry'],
                'contact_phone'=> $data['phone'],
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'      => 'Client registered successfully',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user->load('client')
            ], 201);
        });
    }
}