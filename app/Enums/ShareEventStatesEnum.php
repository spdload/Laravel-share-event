<?php

namespace App\Enums;

use MadWeb\Enum\Enum;

/**
 * @method static ShareEventStatesEnum INITIAL()
 * @method static ShareEventStatesEnum CONNECTED()
 * @method static ShareEventStatesEnum CANCELED()
 * @method static ShareEventStatesEnum CONFIRMATION_WAITING()
 * @method static ShareEventStatesEnum RESCHEDULING()
 * @method static ShareEventStatesEnum CONFIRMED()
 * @method static ShareEventStatesEnum REVIEW_WAITING()
 * @method static ShareEventStatesEnum COMPLETED()
 */
final class ShareEventStatesEnum extends Enum
{
    public const INITIAL = 'new';
    public const CONNECTED = 'connected';
    public const CANCELED = 'canceled';
    public const CONFIRMATION_WAITING = 'confirmation-waiting';
    public const RESCHEDULING = 'rescheduling';
    public const CONFIRMED = 'confirmed';
    public const REVIEW_WAITING = 'review-waiting';
    public const COMPLETED = 'completed';
}
