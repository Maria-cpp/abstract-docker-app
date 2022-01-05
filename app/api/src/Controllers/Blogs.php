<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Common\Config\ProgramConfig;
use App\Common\Database\Primary\Posts;
use App\Common\Posts\Post;
use App\Common\Exception\API_Exception;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Packages\ReCaptcha\ReCaptcha;
use App\Common\Validator;
use Comely\Database\Schema;
use Comely\DataTypes\Integers;
use Comely\Utils\Security\Passwords;


class Blogs extends AbstractSessionAPIController{

    public function sessionAPICallback(): void
    {

    }

}