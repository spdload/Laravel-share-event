<?php

namespace App\Nova;

use App\Models\ShareEventFeedback as ShareEventFeedbackModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class ShareEventFeedback extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ShareEventFeedbackModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'additional_information',
        'type',
    ];

    public static function label()
    {
        return __('Share Event Feedback');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('User')->sortable(),
            Text::make('Message', 'type')->sortable(),
            Text::make('Type', function () {
                return $this->shareEvent->getActiveType($this->user_id);
            }),
            Text::make('Additional Message', 'additional_information'),
        ];
    }
}
