<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    private string $token;

    public function __construct($resource, string $token = '')
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role->value,
            'role_label' => $this->role->label(),
            'phone'      => $this->phone,
            'avatar_url' => $this->avatar_url,
            'is_active'  => $this->is_active,
            'token'      => $this->token ?: null,
        ];
    }
}