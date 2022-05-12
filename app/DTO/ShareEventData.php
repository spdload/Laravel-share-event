<?php

namespace App\DTO;

use App\Models\PlantVariety;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\DataTransferObject\DataTransferObject;

class ShareEventData extends DataTransferObject
{
    public string $type;
    public int $count;
    public string $size;
    public string $comment;
    public User $initiator;
    public User $respondent;
    public PlantVariety $plantVariety;

    public static function fromRequest(Request $request)
    {
        return new self([
           'type' => $request->input('type'),
           'count' => (int) $request->input('count'),
           'size' => $request->input('size'),
           'comment' => $request->input('comment'),
           'initiator' => $request->user(),
           'respondent' => User::find($request->input('respondent_id')),
           'plantVariety' => PlantVariety::find($request->input('plant_variety_id')),
       ]);
    }
}
