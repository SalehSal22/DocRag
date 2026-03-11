<?php

namespace App\Http\Resources\AuthResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'data' => [
                'name' => $this->resource['user']->name,
                'email' => $this->resource['user']->email,
                'accessToken' => $this->resource['newAccessToken'],
                'refresh_token' => $this->resource['newRefresh_token']
            ]
        ];
    }
}
