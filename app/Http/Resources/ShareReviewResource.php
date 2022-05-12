<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ShareReview */
class ShareReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'image' => $this->image,
            'is_read' => $this->is_read,
            'image' => $this->image,
        ];
    }
}
