<?php
declare(strict_types=1);

namespace App\Common\Database\Primary;

use App\Common\Database\AbstractAppTable;
use App\Common\Exception\AppException;
use App\Common\Kernel;
use Comely\Database\Exception\ORM_ModelNotFoundException;
use Comely\Database\Schema\Table\Columns;
use Comely\Database\Schema\Table\Constraints;

/**
 * Class Users
 * @package App\Common\Database\Primary
 */
class Employees extends AbstractAppTable
{
    public const NAME = 'employee';
    public const MODEL = 'App\Common\Users\Employe';
    public const BINARY_OBJ_SIZE = 4096;

    /**
     * @param Columns $cols
     * @param Constraints $constraints
     */
    public function structure(Columns $cols, Constraints $constraints): void
    {
        $cols->defaults("ascii", "ascii_general_ci");

        $cols->int("id")->bytes(4)->unSigned()->autoIncrement();
        $cols->int("user_id")->bytes(4)->unSigned()->nullable();
        $cols->string("first_name")->length(32)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci");
        $cols->string("last_name")->length(32)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci");
        $cols->string("email")->length(64)->unique();
        $cols->int("is_email_verified")->bytes(1)->default(0);
        $cols->string("country")->fixed(3);
        $cols->string("phone_sms")->length(24)->nullable();
        $cols->int("time_stamp")->bytes(4)->unSigned();
        $cols->primaryKey("id");

        $constraints->foreignKey("user_id")->table(Users::NAME, "id");
        $constraints->foreignKey("country")->table(Countries::NAME, "code");
    }

    public static function List(?string $name = null): array
    {
        $query = 'WHERE 1 ORDER BY `id` ASC';
        $queryData = null;
        if (is_string($name) && $name !="") {
            $query = 'WHERE `first_name`=? ORDER BY `id` ASC';
            $queryData = [$name];
        }

        try {
            return Customers::Find()->query($query, $queryData)->all();
        } catch (\Exception $e) {
            Kernel::getInstance()->errors()->trigger($e, E_USER_WARNING);
        }

        return [];
    }

}
