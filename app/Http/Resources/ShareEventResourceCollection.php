<?php

namespace App\Http\Resources;

use App\Enums\MeetingPlaceTypesEnum;
use App\Enums\PlantVarietySizesEnum;
use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareMethodsEnum;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\ShareEvent */
class ShareEventResourceCollection extends ResourceCollection
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => ShareEventResource::collection($this->collection),
        ];
    }

    public function with($request)
    {
        $clientStates = ShareEventClientStatesEnum::labels();
        unset($clientStates[ShareEventClientStatesEnum::REVIEW_SENT], $clientStates[ShareEventClientStatesEnum::COMPLETED]);

        return [
            'meta' => [
                'sizes' => PlantVarietySizesEnum::labels(),
                'state' => $clientStates,
                'meeting_place_types' => MeetingPlaceTypesEnum::labels(),
                'share_methods' => ShareMethodsEnum::labels(),
            ],
        ];
    }
}
