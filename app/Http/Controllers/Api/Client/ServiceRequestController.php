<?php
namespace App\Http\Controllers\Api\Client;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Http\Resources\ServiceRequestResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    // List all requests belonging to the logged-in company
    public function index()
    {
        $client = Auth::user()->client;
        $requests = ServiceRequest::where('client_id', $client->id)
            ->with('assignedStaff.user') // Load guards so client can see them
            ->latest()
            ->paginate(10);

        return ServiceRequestResource::collection($requests);
    }

    // Submit a new request (The Figma Form)
   public function store(Request $request)
        {
            $data = $request->validate([
                
                'title' => 'required|string|max:255',
                'category' => 'required|in:personal,event,business,vip,armed,unarmed,escort,event',
                'description' => 'required|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'required_staff_count' => 'required|integer|min:1',
            ]);

            $user = Auth::user();
            $client = $user->client;

            // CHECK IF CLIENT EXISTS
            if (!$client) {
                return response()->json([
                    'message' => 'Profile not found.',
                    'errors' => ['general' => ['This user account is not registered as a Client profile.']]
                ], 404);
            }

            $serviceRequest = $client->requests()->create([
                'uuid' => (string) Str::uuid(),
                'title' => $data['title'],
                'category' => $data['category'],
                'description' => $data['description'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'required_staff_count' => $data['required_staff_count'],
                'status' => 'pending',
            ]);

            return new ServiceRequestResource($serviceRequest);
        }

    // Show a single request with progress tracking
    public function show($uuid)
    {
        $client = Auth::user()->client;
        $request = ServiceRequest::where('uuid', $uuid)
            ->where('client_id', $client->id)
            ->with('assignedStaff.user')
            ->firstOrFail();

        return new ServiceRequestResource($request);
    }
}