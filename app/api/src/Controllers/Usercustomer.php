<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Common\Config\ProgramConfig;
use App\Common\Database\Primary\Customers;
use App\Common\Users\Customer;
use App\Common\Exception\API_Exception;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Packages\ReCaptcha\ReCaptcha;
use App\Common\Validator;
use Comely\Database\Schema;
use Comely\DataTypes\Integers;
use Comely\Utils\Security\Passwords;

/**
 * Class Signup
 * @package App\API\Controllers
 */
class Usercustomer extends AbstractSessionAPIController
{
    /**
     * @throws API_Exception
     */
    public function sessionAPICallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Customers');

    }

    /**
     * @throws API_Exception
     * @throws AppException
     * @throws \Comely\Database\Exception\DatabaseException
     */
    public function get(): void
    {
        $Customers = \App\Common\Database\Primary\Customers::List("active");

        $this->status(true);
        $this->response()->set("Customers", $Customers);
    }

    public function post(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Countries');
        Schema::Bind($db, 'App\Common\Database\Primary\Users');
        Schema::Bind($db, 'App\Common\Database\Primary\Customers');
        // ReCaptcha Validation
        if ($this->isReCaptchaRequired()) {
            try {
                $reCaptchaRes = $this->input()->get("reCaptchaRes");
                if (!$reCaptchaRes || !is_string($reCaptchaRes)) {
                    throw new API_Exception('RECAPTCHA_REQ');
                }

                $programConfig = ProgramConfig::getInstance();
                $reCaptchaSecret = $programConfig->reCaptchaPrv;
                if (!$reCaptchaSecret || !is_string($reCaptchaSecret)) {
                    throw new AppException('ReCaptcha secret was not available');
                }

                try {
                    ReCaptcha::Verify($reCaptchaSecret, $reCaptchaRes, $this->ipAddress);
                } catch (\Exception $e) {
                    throw new API_Exception('RECAPTCHA_FAILED');
                }
            } catch (API_Exception $e) {
                $e->setParam("reCaptchaRes");
                throw $e;
            }
        }

        // First name
        try {
            $firstName = trim(strval($this->input()->get("firstName")));
            if (!$firstName) {
                throw new API_Exception('FIRST_NAME_REQ');
            } elseif (!Integers::Range(strlen($firstName), 3, 16)) {
                throw new API_Exception('FIRST_NAME_LEN');
            } elseif (!preg_match('/^[a-z]+(\s[a-z]+)*$/i', $firstName)) {
                throw new API_Exception('FIRST_NAME_INVALID');
            }
        } catch (AppException $e) {
            $e->setParam("firstName");
            throw $e;
        }

        // Last name
        try {
            $lastName = trim(strval($this->input()->get("lastName")));
            if (!$lastName) {
                throw new API_Exception('LAST_NAME_REQ');
            } elseif (!Integers::Range(strlen($lastName), 2, 16)) {
                throw new API_Exception('LAST_NAME_LEN');
            } elseif (!preg_match('/^[a-z]+(\s[a-z]+)*$/i', $lastName)) {
                throw new API_Exception('LAST_NAME_INVALID');
            }
        } catch (AppException $e) {
            $e->setParam("lastName");
            throw $e;
        }

        // E-mail Address
        try {
            $email = trim(strval($this->input()->get("email")));
            if (!$email) {
                throw new API_Exception('EMAIL_ADDR_REQ');
            } elseif (!Validator::isValidEmailAddress($email)) {
                throw new API_Exception('EMAIL_ADDR_INVALID');
            } elseif (strlen($email) > 64) {
                throw new API_Exception('EMAIL_ADDR_LEN');
            }

            // Duplicate check
            $dup = $db->query()->table(Customers::NAME)
                ->where('`email`=?', [$email])
                ->fetch();
            if ($dup->count()) {
                throw new API_Exception('EMAIL_ADDR_DUP');
            }
        } catch (AppException $e) {
            $e->setParam("email");
            throw $e;
        }

        // Country
        try {
            $country = trim(strval($this->input()->get("country")));
            if (strlen($country) !== 3) {
                throw new API_Exception('COUNTRY_INVALID');
            }

            try {
                $country = \App\Common\Database\Primary\Countries::get($country);
            } catch (AppException $e) {
                throw new API_Exception('COUNTRY_INVALID');
            }

            if ($country->status !== 1) {
                throw new API_Exception('COUNTRY_INVALID');
            }
        } catch (API_Exception $e) {
            $e->setParam("country");
            throw $e;
        }

        // Password
        try {
            $password = trim(strval($this->input()->get("password")));
            $passwordLen = strlen($password);
            if (!$password) {
                throw new API_Exception('PASSWORD_REQ');
            } elseif ($passwordLen <= 5) {
                throw new API_Exception('PASSWORD_LEN_MIN');
            } elseif ($passwordLen > 32) {
                throw new API_Exception('PASSWORD_LEN_MAX');
            } elseif (Passwords::Strength($password) < 4) {
                throw new API_Exception('PASSWORD_WEAK');
            }
        } catch (AppException $e) {
            $e->setParam("password");
            throw $e;
        }

        // Confirm Password
        try {
            $confirmPassword = trim(strval($this->input()->get("confirmPassword")));
            if ($password !== $confirmPassword) {
                throw new API_Exception('PASSWORD_CONFIRM_MATCH');
            }
        } catch (API_Exception $e) {
            $e->setParam("confirmPassword");
            throw $e;
        }

        // Insert Customer?
        try {
            $db->beginTransaction();

            $apiHmacSecret = Passwords::Generate(16);
            $customer = new Customer();
            $customer->id = 0;
            $customer->status = "active";
            $customer->firstName = $firstName;
            $customer->lastName = $lastName;
            $customer->email = $email;
            $customer->isEmailVerified = 0;
            $customer->country = $country->code;
            $customer->phoneSms = null;
            $customer->timeStamp = time();

            $customer->query()->insert(function () {
                throw new AppControllerException('Failed to insert user row');
            });

            $db->commit();
        } catch (AppException $e) {
            $db->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $db->rollBack();
            $this->app->errors()->triggerIfDebug($e, E_USER_WARNING);
            throw API_Exception::InternalError();
        }

        $this->status(true);
        $this->response()->set("customer", $customer);
    }
}
