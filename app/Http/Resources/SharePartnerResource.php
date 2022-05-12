<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class SharePartnerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->getNameOrPlaceholder($this->state),
            'first_name' =>$this->first_name,
            'last_name' =>$this->last_name,
            'avatar' => $this->getAvatarOrPlaceholder($this->state),
            'neighborhood' => $this->neighborhood,
            'address' => $this->address,
            'is_private' => $this->is_private,
            'distance' => $this->when($this->distance !== null, function () {
                return round(meters_to_miles($this->distance));
            }),
        ];
    }
}
