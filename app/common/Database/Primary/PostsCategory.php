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
 * Class PostsCategory
 * @package App\Common\Database\Primary
 */
class PostsCategory extends AbstractAppTable
{
    public const NAME = 'postcategory';
    public const MODEL = 'App\Common\Category\Category';
    public const BINARY_OBJ_SIZE = 4096;

    /**
     * @param Columns $cols
     * @param Constraints $constraints
     */
    public function structure(Columns $cols, Constraints $constraints): void
    {
        $cols->defaults("ascii", "ascii_general_ci");

        $cols->int("id")->bytes(4)->unSigned()->autoIncrement();

        $cols->string("name")->length(100)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci")->default("general");

        $cols->primaryKey("id");
    }
   

    public static function List(?string $name = null): array
    {
        $query = 'WHERE 1 ORDER BY `id` ASC';
        $queryData = null;
        if (is_string($name) && $name !="") {
            $query = 'WHERE `name`=? ORDER BY `id` ASC';
            $queryData = [$name];
        }

        try {
            return PostsCategory::Find()->query($query, $queryData)->all();
        } catch (\Exception $e) {
            Kernel::getInstance()->errors()->trigger($e, E_USER_WARNING);
        }

        return [];
    }
}
