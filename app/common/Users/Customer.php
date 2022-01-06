<?php
declare(strict_types=1);

namespace App\Common\Users;

use App\Common\Database\AbstractAppModel;
use App\Common\Exception\AppException;
use App\Common\Validator;
use Comely\DataTypes\Buffer\Binary;

/**
 * Class Customer
 * @package App\Common\Users
 */
class Customer extends AbstractAppModel
{
    public const TABLE = \App\Common\Database\Primary\Customers::NAME;

    /** @var int */
    public int $id;    
    /** @var string */
    public string $status;
    /** @var string */
    public string $firstName;
    /** @var string */
    public string $lastName;
    /** @var string */
    public string $email;
    /** @var int */
    public int $isEmailVerified;
    /** @var string */
    public string $country;
    /** @var null|string */
    public ?string $phoneSms = null;
    /** @var int */
    public int $timeStamp;

    /**
     * @return void
     */

    /**
     * @return string|null
     * @throws AppException
     */
    // public function onSerialize(): void
    // {
    //     parent::onSerialize();
    // }

    public function smsPhoneNum(): ?string
    {
        if (!$this->phoneSms) {
            return null;
        }

        if (!Validator::isValidPhone($this->phoneSms)) {
            throw new AppException(sprintf('Invalid user # %d SMS phone number', $this->id));
        }

        return $this->phoneSms;
    }

    /**
     * @return Binary
     * @throws AppException
     */
    public function emailVerifyBytes(): Binary
    {
        return $this->cipher()->pbkdf2("sha1", $this->email, 0x21a);
    }


}
