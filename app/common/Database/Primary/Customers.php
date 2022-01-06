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
class Customers extends AbstractAppTable
{
    public const NAME = 'customers';
    public const MODEL = 'App\Common\Users\Customer';
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
        $cols->enum("status")->options("active", "frozen", "disabled")->default("active");
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

    /**
     * @param int $id
     * @param bool $cache
     * @return Customer
     * @throws AppException
     */

    // public static function get(int $id): Customer
    // {
    //     $k = Kernel::getInstance();

    //     try {
    //         return $k->memory()->query(sprintf('customers_%s', $id), self::MODEL)
    //             ->fetch(function () use ($id) {
    //                 return self::Find()->col("id", $id)->limit(1)->first();
    //             });
    //     } catch (\Exception $e) {
    //         if (!$e instanceof ORM_ModelNotFoundException) {
    //             $k->errors()->triggerIfDebug($e, E_USER_WARNING);
    //         }

    //         throw new AppException('No such customer is available');
    //     }
    // }
    public static function List(?string $status = null): array
    {
        $query = 'WHERE 1 ORDER BY `id` ASC';
        $queryData = null;
        if (is_string($status) && $status !="") {
            $query = 'WHERE `status`=? ORDER BY `id` ASC';
            $queryData = [$status];
        }

        try {
            return Customers::Find()->query($query, $queryData)->all();
        } catch (\Exception $e) {
            Kernel::getInstance()->errors()->trigger($e, E_USER_WARNING);
        }

        return [];
    }

}
