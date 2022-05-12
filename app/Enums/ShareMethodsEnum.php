<?php

namespace App\Enums;

use MadWeb\Enum\Enum;

/**
 * @method static ShareMethodsEnum WORK_TOGETHER()
 * @method static ShareMethodsEnum I_WILL_SUPERVISE()
 * @method static ShareMethodsEnum THEY_WILL_PICK_IT_UP()
 */
class ShareMethodsEnum extends Enum
{
    public const WORK_TOGETHER = 'work-together';
    public const I_WILL_SUPERVISE = 'i-will-supervise';
    public const THEY_WILL_PICK_IT_UP = 'they-will-pick-it-up';
}
