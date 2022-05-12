<?php

namespace App\Models;

use App\DTO\ShareMessageData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;

class ShareMessage extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $fillable = [
        'message',
        'images',
        'is_read',
        'is_notification_received',
    ];

    protected $attributes = [
        'is_read' => false,
        'is_notification_received' => false,
    ];

    protected $appends = ['images'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareEvent(): BelongsTo
    {
        return $this->belongsTo(ShareEvent::class);
    }

    /**
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig
     */
    public function setImagesAttribute(array $images)
    {
        foreach ($images as $image) {
            $this->addMedia($image)->toMediaCollection();
        }
    }

    public function getImagesAttribute(): array
    {
        return $this->getMedia()->map(function (Media $media) {
            return $media->getUrl();
        })->toArray();
    }

    public static function createForShareEvent(ShareEvent $shareEvent, ShareMessageData $shareMessageData): self
    {
        $shareMessage = self::make($shareMessageData->only('message', 'images')->toArray());

        $shareMessage->user()->associate($shareMessageData->user);
        $shareMessage->shareEvent()->associate($shareEvent);
        $shareMessage->save();

        return $shareMessage;
    }
}
