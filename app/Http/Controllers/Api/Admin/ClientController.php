<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('user');

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('company_name', 'like', "%{$request->search}%");
        }

        return ClientResource::collection($query->latest()->paginate($request->per_page ?? 10));
    }
        public function show($uuid)
            {
                $client = Client::where('uuid', $uuid)->firstOrFail();
                return new ClientResource($client);
            }

        public function store(Request $request)
        {
            $data = $request->validate([
                'first_name'   => 'required|string',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:6',
                'company_name' => 'required|string',
                'photo'        => 'nullable|image|max:2048', // 2MB Max
            ]);

             \Log::info('===  REQUEST START ===', [
    
        'user_id'   => auth()->id(),
        'input'     => $request->all(),
        'files'     => $request->allFiles() ? array_keys($request->allFiles()) : [],
    ]);

            return DB::transaction(function () use ($request) {
                // 1. Create User
                $user = User::create([
                    'uuid'       => (string) Str::uuid(),
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'phone'      => $request->phone,
                    'address'    => $request->address,
                    'password'   => Hash::make($request->password),
                    'type'       => 'client',
                ]);

                // 2. Handle Photo Upload
                if ($request->hasFile('photo')) {
                    $path = $request->file('photo')->store('profile_photos', 'public');
                    $user->update(['photo' => $path]);
                }

                // 3. Create Client linked to User
                $client = $user->client()->create([
                    'uuid'                => (string) Str::uuid(),
                    'company_name'        => $request->company_name,
                    'address'        => $request->address,
                    'contact_phone'        => $request->phone,
                    'industry'            => $request->industry,
                    // 'registration_number' => $request->registration_number,
                ]);

                return new ClientResource($client);
            });
        }

        public function update(Request $request, $uuid)
        {
            $client = Client::where('uuid', $uuid)->with('user')->firstOrFail();
            $user = $client->user;

            $request->validate([
                'email' => 'required|email|unique:users,email,' . $user->id,
                'photo' => 'nullable|image|max:2048',
            ]);

            DB::transaction(function () use ($request, $user, $client) {
                // 1. Update User text fields
                $user->update($request->only(['first_name', 'last_name', 'email', 'phone', 'address']));
                
                // 2. Update Password if provided
                if ($request->filled('password')) {
                    $user->update(['password' => Hash::make($request->password)]);
                }

                // 3. Handle Photo Update (Delete old, Save new)
                if ($request->hasFile('photo')) {
                    if ($user->photo) {
                        Storage::disk('public')->delete($user->photo);
                    }
                    $path = $request->file('photo')->store('profile_photos', 'public');
                    $user->update(['photo' => $path]);
                }

                // 4. Update Client fields
                $client->update($request->only(['company_name', 'industry', 'registration_number']));
            });

            return new ClientResource($client->fresh());
        }
    public function destroy($uuid)
    {
        $client = Client::where('uuid', $uuid)->firstOrFail();
        // This will delete the user and the client due to cascade or manual delete
        $client->user->delete(); 
        return response()->json(['message' => 'Client deleted successfully']);
    }
}