<?php

namespace App\Http\Controllers\Api;

use App\DTO\ShareEventData;
use App\Enums\ShareEventStatesEnum;
use App\Filters\FiltersShareEventByStates;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateShareEventRequest;
use App\Http\Requests\Api\UpdateShareEventRequest;
use App\Http\Requests\GetShareEventRequest;
use App\Http\Resources\ShareEventResource;
use App\Http\Resources\ShareEventResourceCollection;
use App\Models\ShareEvent;
use App\Notifications\ConfirmUpdateShareEventNotification;
use App\States\ShareEvent\ConfirmationWaiting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ShareEventsController extends Controller
{
    /** @var int */
    private const RESOURCES_PER_PAGE = 6;

    public function __construct()
    {
        $this->middleware('verified', [
            'only' => 'store',
        ]);

        $this->authorizeResource(ShareEvent::class, 'shareEvent');
    }

    public function index(GetShareEventRequest $request)
    {
        $shareEventsQuery = QueryBuilder::for(ShareEvent::class)
            ->allowedFilters([
                AllowedFilter::custom('state', new FiltersShareEventByStates($request->user()->id)),
                AllowedFilter::custom('type', new FiltersShareEventByStates($request->user()->id)),
            ])->where(function (Builder $query) use ($request) {
                $query->where('initiator_id', $request->user()->id)
                    ->orWhere('respondent_id', $request->user()->id);
            })->where('state', '<>', ShareEventStatesEnum::CANCELED);

        $request->boolean('completed')
            ? $shareEventsQuery->where('state', ShareEventStatesEnum::COMPLETED)
            : $shareEventsQuery->where('state', '<>', ShareEventStatesEnum::COMPLETED);

        if ($request->filled('search')) {
            $searchData = $request->get('search');

            $shareEventsQuery->where(function (Builder $query) use ($searchData) {
                $query->whereLike([
                    'plantVariety.name',
                    'initiator.first_name',
                    'initiator.last_name',
                    'respondent.first_name',
                    'respondent.last_name',
                ], $searchData)
                    ->orWhereHas('plantVariety', function ($query) use ($searchData) {
                        $query->whereLike('species.name', $searchData);
                    })->orWhereHas('plantVariety.species', function ($query) use ($searchData) {
                        $query->whereLike(['commonNames.name', 'genus.name'], $searchData);
                    })->orWhereHas('plantVariety.species.genus', function ($query) use ($searchData) {
                        $query->whereLike('family.name', $searchData);
                    });
            });
        }

        return new ShareEventResourceCollection(
            $shareEventsQuery
                ->with([
                    'plantVariety.species.commonNames',
                    'reviews',
                    'initiator',
                    'respondent',
                ])->orderByDesc('created_at')->paginate(self::RESOURCES_PER_PAGE)
        );
    }

    public function store(CreateShareEventRequest $request): JsonResource
    {
        return new ShareEventResource(
            ShareEvent::createBetweenUsersWithAttributes(
                ShareEventData::fromRequest($request)
            )
        );
    }

    public function show(Request $request, ShareEvent $shareEvent): JsonResource
    {
        $shareEventResource = new ShareEventResource(clone $shareEvent);

        if ($shareEvent->isChanged($request->user()->id)) {
            $request->user()->is($shareEvent->initiator)
                ? $shareEvent->update(['is_changed_by_initiator' => false])
                : $shareEvent->update(['is_changed_by_respondent' => false]);
        }

        $shareEvent->readFeedbackBy($request->user()->id);

        return $shareEventResource;
    }

    public function update(UpdateShareEventRequest $request, ShareEvent $shareEvent)
    {
        $shareEvent->update($request->only(['count', 'size']));

        if (! $shareEvent->state->is(ConfirmationWaiting::class)) {
            $shareEvent->state->transitionTo(ConfirmationWaiting::class);
        }

        if ($shareEvent->wasChanged('count') || $shareEvent->wasChanged('size')) {
            $request->user()->notify(
                new ConfirmUpdateShareEventNotification(
                    $shareEvent,
                    $request->user(),
                    $shareEvent->getSharePartner($request->user()->getKey())->first_name,
                    $shareEvent->wasChanged('count'),
                    $shareEvent->wasChanged('size'),
                )
            );
        }

        return new ShareEventResource($shareEvent);
    }

    public function destroy(ShareEvent $shareEvent)
    {
        $shareEvent->delete();

        return response()->noContent();
    }
}
