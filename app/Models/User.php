<?php

namespace App\Models;

use App\Enums\MissingImagesEnum;
use App\Enums\PlantActivityTypesEnum;
use App\Enums\ShareEventStatesEnum;
use App\Enums\UserRolesEnum;
use App\Http\Controllers\Api\Auth\Traits\MustVerifyNewEmailTrait;
use App\Models\User\UserAttributes;
use App\Notifications\CompletionRegistrationNotification;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use MadWeb\Seoable\Contracts\Seoable;
use MadWeb\Seoable\Traits\SeoableTrait;
use MadWeb\SocialAuth\Contracts\SocialAuthenticatable;
use MadWeb\SocialAuth\Traits\UserSocialite;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class User extends Authenticatable implements
    HasMedia,
    Seoable,
    MustVerifyEmail,
    SocialAuthenticatable
{
    use Notifiable;
    use SeoableTrait;
    use HasMediaTrait;
    use UserAttributes;
    use UserSocialite;
    use SpatialTrait;
    use MustVerifyNewEmailTrait;

    public const USER_PRIVATE_PLACEHOLDER = "Hi! I'm a bit shy...";

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'is_terms_read',
        'is_private',
        'is_private_warning',
        'need_feedback',
        'address',
        'neighborhood',
        'max_send_plant_distance',
        'max_receive_plant_distance',
        'email_verified_at',
        'my_garden_plants',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'email_verified_at',
    ];

    protected $appends = [
        'avatar',
    ];

    protected $attributes = [
        'role' => UserRolesEnum::__default,
        'is_private' => false,
        'is_private_warning' => true,
        'need_feedback' => true,
        'max_send_plant_distance' => 50,
        'max_receive_plant_distance' => 50,
        'timezone' => '+00:00',
    ];

    protected $spatialFields = [
        'location',
    ];

    protected $casts = [
        'max_send_plant_distance' => 'integer',
        'max_receive_plant_distance' => 'integer',
        'is_private' => 'boolean',
    ];

    public function mapSocialData(\Laravel\Socialite\Contracts\User $socialUser)
    {
        $name = $socialUser->getName() ?? $socialUser->getNickname();
        $name = $name ?? $socialUser->getEmail();
        $nameParts = explode(' ', trim($name));

        return [
            $this->getEmailField() => $socialUser->getEmail(),
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? $nameParts[0],
            'email_verified_at' => Carbon::now(),
            'role' => UserRolesEnum::MEMBER,
        ];
    }

    /**
     * Declare metatags data.
     */
    public function seoable()
    {
        $this->seo()
            ->setTitle('name')
            ->setDescription('name');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CompletionRegistrationNotification($this->full_name));
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->full_name));
    }

    public function setAvatar($avatar)
    {
        $this->removeAvatar();

        if (is_string($avatar)) {
            $this->addMediaFromUrl($avatar);
        } else {
            $this->addMedia($avatar)->toMediaCollection('avatar');
        }
    }

    public function removeAvatar()
    {
        return $this->clearMediaCollection('avatar');
    }

    public function getAvatarUrl()
    {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }

    public function hasAvatar()
    {
        return $this->hasMedia('avatar');
    }

    public function plantVarieties()
    {
        return $this->belongsToMany(PlantVariety::class)
            ->withPivot('activity_type', 'id')
            ->withTimestamps();
    }

    public function detachPlantVarieties(array $plantVerities)
    {
        $plantVarietiesFormatted = collect($plantVerities)->map(function ($plantVariety) {
            return (int) $plantVariety['id'];
        })->toArray();

        $this->plantVarieties()->detach($plantVarietiesFormatted);
    }

    public function attachPlantVarieties(array $plantVerities)
    {
        $plantVarietiesFormatted = collect($plantVerities)->flatMap(function ($plantVariety) {
            return collect($plantVariety['types'])->map(function ($type) use ($plantVariety) {
                return
                    [
                        'plant_variety_id' => (int) $plantVariety['id'],
                        'activity_type' => $type,
                    ];
            })->reject(function ($type) {
                return empty($type['activity_type']);
            });
        })->toArray();

        $this->plantVarieties()->attach($plantVarietiesFormatted);
    }

    public function shareCancelReasons(): HasMany
    {
        return $this->hasMany(PlantShareCancelReason::class);
    }

    public function superHumanFeedbacks(): HasMany
    {
        return $this->hasMany(SuperHumanFeedback::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShareReview::class);
    }

    public function shareEventFeedback()
    {
        return $this->hasMany(ShareEventFeedback::class);
    }

    public function shareMessages(): HasMany
    {
        return $this->hasMany(ShareMessage::class);
    }

    public function plantRequests(): HasMany
    {
        return $this->hasMany(PlantRequest::class);
    }

    public function plantIdentifications(): HasMany
    {
        return $this->hasMany(PlantIdentification::class);
    }

    public function climateZone(): BelongsTo
    {
        return $this->belongsTo(ClimateZone::class);
    }

    public function canJoinShareEventChat(ShareEvent $shareEvent): bool
    {
        return $shareEvent->respondent_id === $this->id
            || $shareEvent->initiator_id === $this->id;
    }

    public function pendingEmails(): HasMany
    {
        return $this->hasMany(PendingUserEmail::class);
    }

    public function isPlantGiver(ShareEvent $shareEvent): bool
    {
        return PlantActivityTypesEnum::SHARE()->is($shareEvent->type)
            ? $this->id === $shareEvent->initiator->id
            : $this->id === $shareEvent->respondent->id;
    }

    public function getNameOrPlaceholder(?string $state): string
    {
        return ($this->is_private && $state === ShareEventStatesEnum::INITIAL) || ($this->is_private && ! $state)
            ? self::USER_PRIVATE_PLACEHOLDER
            : $this->full_name;
    }

    public function getAvatarOrPlaceholder(?string $state): string
    {
        return ($this->is_private && $state === ShareEventStatesEnum::INITIAL) || ($this->is_private && ! $state)
            ? missing_image(MissingImagesEnum::AVATAR)
            : $this->avatar;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRolesEnum::ADMIN;
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getNewMessagesCountAttribute()
    {
        return ShareMessage::whereHas('shareEvent', function ($query) {
            $query->where('initiator_id', $this->id)->orWhere('respondent_id', $this->id);
        })
            ->where('user_id', '<>', $this->id)->where('is_read', false)
            ->count();
    }
}
