<?php

namespace App\Enums;

use MadWeb\Enum\Enum;

/**
 * @method static ShareEventClientStatesEnum REQUEST_SENT()
 * @method static ShareEventClientStatesEnum INVITATION_SENT()
 * @method static ShareEventClientStatesEnum NEW_REQUEST()
 * @method static ShareEventClientStatesEnum NEW_INVITATION()
 * @method static ShareEventClientStatesEnum CONNECTED()
 * @method static ShareEventClientStatesEnum CONFIRMATION_WAITING()
 * @method static ShareEventClientStatesEnum REPLY_WAITING()
 * @method static ShareEventClientStatesEnum RESCHEDULING()
 * @method static ShareEventClientStatesEnum REVIEW_WAITING()
 * @method static ShareEventClientStatesEnum REVIEW_SENT()
 * @method static ShareEventClientStatesEnum COMING_RIGHT_UP()
 * @method static ShareEventClientStatesEnum REVIEW_RECEIVED()
 * @method static ShareEventClientStatesEnum COMPLETED()
 */
final class ShareEventClientStatesEnum extends Enum
{
    // I'll Love
    public const REQUEST_SENT = 'request-sent';
    public const NEW_INVITATION = 'new-invitation';
    public const REPLY_WAITING = 'reply-waiting';

    //I'll Share
    public const INVITATION_SENT = 'invitation-sent';
    public const NEW_REQUEST = 'new-request';
    public const CONFIRMATION_WAITING = 'confirmation-waiting';

    // General
    public const CONNECTED = 'connected';
    public const RESCHEDULING = 'rescheduling';
    public const REVIEW_WAITING = 'review-waiting';
    public const COMING_RIGHT_UP = 'coming-right-up';
    public const REVIEW_SENT = 'review-sent';
    public const REVIEW_RECEIVED = 'review-received';
    public const COMPLETED = 'completed';
}
