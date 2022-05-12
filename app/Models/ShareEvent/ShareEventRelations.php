<?php

namespace App\Models\ShareEvent;

use App\Models\PlantVariety;
use App\Models\ShareEventFeedback;
use App\Models\ShareMessage;
use App\Models\ShareReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ShareEventRelations
{
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plantVariety(): BelongsTo
    {
        return $this->belongsTo(PlantVariety::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShareReview::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ShareMessage::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ShareEventFeedback::class);
    }
}
