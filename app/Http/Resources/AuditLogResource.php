<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'description'  => $this->description, // (updated, created, deleted)

            'entity_type'  => $this->subject_type ? class_basename($this->subject_type) : null,
            'entity_id'    => $this->subject_id,

            'causer'       => $this->causer,

            'old_values'   => $this->properties->get('old'),
            'new_values'   => $this->properties->get('attributes'),

            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}
