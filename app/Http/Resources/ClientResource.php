<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{

    public function toArray(Request $request): array
{
    return [
        'uuid'       => $this->uuid,
        'first_name' => $this->user->first_name ?? '',
        'last_name'  => $this->user->last_name ?? '',
        'email'      => $this->user->email ?? '',
        'phone'      => $this->user->phone ?? '',
        'address'    => $this->user->address ?? '',
        'company_name' => $this->company_name,
        'industry'     => $this->industry,
        'registration_number' => $this->registration_number,
        'photo'      => $this->user->photo_url ?? null, // Uses User model accessor
        'created_at' => $this->created_at->format('d M Y'),
    ];
}


}