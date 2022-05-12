<?php

namespace App\Http\Resources;

use App\Models\ShareEvent;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShareEvent
 */
class ShareEventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'species_latin_name' => $this->plantVariety->species->name,
            'species_common_names' => PlantSpeciesCommonNameResource::collection($this->plantVariety->species->commonNames),
            'is_complex_species' => $this->plantVariety->species->isComplex(),
            'plant_variety' => [
                'id' => $this->plantVariety->id,
                'name' => $this->plantVariety->name,
                'image' => $this->plantVariety->image,
            ],
            'type' => $this->getActiveType($request->user()->id),
            'is_changed' => $this->isChanged($request->user()->id),
            'is_review_missing' => $this->isReviewMissing($request->user()->id),
            'is_occur_readed' => $this->isFeedbackRead($request->user()->id),
            'comment' => $this->comment,
            'share_partner' => (new SharePartnerResource($this->getSharePartner($request->user()->id)
                ->setAttribute('state', $this->getOriginal('state')))),
            'meeting_place' => $this->meeting_place,
            'meeting_place_type' => $this->meeting_place_type,
            'share_method' => $this->share_method,
            'state' => $this->state,
            'client_state' => $this->state->getClientState($request->user()->id),
            'count' => $this->count,
            'size' => $this->size,
            'share_date_at' => $this->share_date,
            'partner_review' => $this->whenLoaded(
                'reviews',
                new ShareReviewResource($this->getPartnerReview($request->user()->id))
            ),
        ];
    }
}
