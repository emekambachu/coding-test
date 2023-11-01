<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'phase_id' => $this->phase_id,
            // Relationships
            'user' => $this->user ? new UserResource($this->user) : '',
            'phase' => $this->phase ? new PhaseResource($this->phase) : '',
        ];
    }
}
