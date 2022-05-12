<?php

namespace App\Models\User;

use App\Enums\MissingImagesEnum;
use App\Enums\UserRolesEnum;
use Illuminate\Http\UploadedFile;

trait UserAttributes
{
    /**
     * Set user avatar.
     *
     * @param UploadedFile|mixed $value
     */
    public function setAvatarAttribute($value)
    {
        if (! $value) {
            $this->removeAvatar();
        } else {
            $this->setAvatar($value);
        }
    }

    /**
     * Get link to user avatar.
     *
     * @param $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return $this->getAvatarUrl() ?? missing_image(MissingImagesEnum::AVATAR);
    }

    /**
     * Provide password encrypting by default.
     *
     * @param string $password
     */
    public function setPasswordAttribute(string $password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function hasRole(UserRolesEnum $role): bool
    {
        return $this->role === $role->getValue();
    }

    public function assignRole(UserRolesEnum $role)
    {
        $this->role = $role->getValue();
    }

    public function getHasLocationAttribute(): bool
    {
        return $this->location;
    }
}
