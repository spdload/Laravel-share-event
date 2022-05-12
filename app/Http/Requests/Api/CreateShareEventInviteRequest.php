<?php

namespace App\Http\Requests\Api;

use App\Enums\MeetingPlaceTypesEnum;
use App\Enums\PlantVarietySizesEnum;
use App\Enums\ShareMethodsEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateShareEventInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createInvite', $this->route('shareEvent'));
    }

    public function rules(): array
    {
        return [
            'count'  => [
                'required',
                'integer',
                'min:1',
                'max:25',
            ],
            'size' => [
                'required',
                'string',
                PlantVarietySizesEnum::rule(),
            ],
            'share_date' => [
                'required',
                'date_format:'.Carbon::DEFAULT_TO_STRING_FORMAT,
            ],
            'meeting_place_type' => [
                'required',
                MeetingPlaceTypesEnum::rule(),
            ],
            'meeting_place' => [
                'required_if:meeting_place_type,'.MeetingPlaceTypesEnum::OTHER(),
                'max:500',
            ],
            'share_method' => [
                'required',
                ShareMethodsEnum::rule(),
            ],
        ];
    }
}
