<?php

namespace App\Models;

use App\Enums\MissingImagesEnum;
use App\Notifications\ReviewReceivedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Traits\Tappable;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class ShareReview extends Model implements HasMedia
{
    use Tappable;
    use HasMediaTrait;

    protected $fillable = [
        'text',
        'is_read',
    ];

    protected $attributes = [
      'is_read' => false,
    ];

    protected $appends = [
        'image',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareEvent(): BelongsTo
    {
        return $this->belongsTo(ShareEvent::class);
    }

    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('default')
            ->useFallbackUrl(missing_image(MissingImagesEnum::PLANT_SPECIES_MAIN()))
            ->singleFile();
    }

    public function sendForPartner(int $userId): void
    {
        $this->shareEvent->initiator_id === $userId
            ? $this->shareEvent->respondent
            ->notify(new ReviewReceivedNotification(
                $this->shareEvent,
                $this->shareEvent->initiator,
                $this->shareEvent->respondent->full_name
            ))

            : $this->shareEvent->initiator
            ->notify(new ReviewReceivedNotification(
                $this->shareEvent,
                $this->shareEvent->respondent,
                $this->shareEvent->initiator->full_name
            ));
    }
}
