<?php

namespace App\Nova;

use App\Enums\ShareEventStatesEnum;
use App\Models\ShareEvent as ShareEventModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Titasgailius\SearchRelations\SearchesRelations;

class ShareEvent extends Resource
{
    use SearchesRelations;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ShareEventModel::class;

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /** @var string */
    public static $orderDirection = 'desc';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'respondent' => ['first_name', 'last_name'],
        'initiator' => ['first_name', 'last_name'],
        'plantVariety' => ['name'],
    ];

    public static $with = [
        'initiator',
        'respondent',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Select::make(__('State'), 'state')->options(ShareEventStatesEnum::labels()),
            Date::make(__('Share Date At'), 'share_date')->sortable(),
            BelongsTo::make(__("I'd Love"), 'respondent', User::class)->sortable(),
            BelongsTo::make(__("I'll Share"), 'initiator', User::class)->sortable(),
            BelongsTo::make(__('Plant Variety'), 'plantVariety', PlantVariety::class),
            Text::make(__('Size'), 'size')->onlyOnDetail(),
            Number::make(__('Count'), 'count')->onlyOnDetail(),
            Textarea::make(__('Comment'), 'comment')->onlyOnDetail(),
            HasMany::make(__('Feedback'), 'feedback', ShareEventFeedback::class),
        ];
    }
}
