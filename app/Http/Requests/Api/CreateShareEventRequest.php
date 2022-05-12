<?php

namespace App\Http\Requests\Api;

use App\Enums\PlantActivityTypesEnum;
use App\Enums\PlantVarietySizesEnum;
use App\Models\User;
use App\Rules\InShareDistanceRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateShareEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    PlantActivityTypesEnum::SHARE(),
                    PlantActivityTypesEnum::LOVE(),
                ]),
            ],
            'plant_variety_id' => [
                'required',
                Rule::exists('plant_variety_has_user')
                    ->where(function (Builder $query) {
                        $query->where('user_id', $this->user()->id);
                        $query->where('activity_type', $this->request->get('type'));
                    }),
            ],
            'respondent_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::exists('plant_variety_has_user', 'user_id')
                    ->where(function (Builder $query) {
                        $query->where('activity_type', $this->getOppositeActivityType($this->request->get('type')));
                        $query->where('plant_variety_id', $this->request->get('plant_variety_id'));
                    }),
                new InShareDistanceRule(
                    $this->user(),
                    User::find($this->request->get('respondent_id')),
                    $this->request->get('type')
                ),
            ],
            'count' => [
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
            'comment' => [
                'required',
                'string',
                'max:325',
            ],
        ];
    }

    private function getOppositeActivityType(string $type)
    {
        $typesMap = [
            PlantActivityTypesEnum::LOVE => PlantActivityTypesEnum::SHARE,
            PlantActivityTypesEnum::SHARE => PlantActivityTypesEnum::LOVE,
        ];

        return $typesMap[$type];
    }
}
