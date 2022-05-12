<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use NumberFormatter;

class SharePartnersCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => SharePartnerResource::collection($this->collection),
            'count' => str_replace(' ', '-', ucfirst(
                NumberFormatter::create(
                    app()->getLocale(),
                    NumberFormatter::SPELLOUT
                )->format($this->collection->count())
            )),
        ];
    }
}
