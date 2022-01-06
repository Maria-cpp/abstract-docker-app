<?php
declare(strict_types=1);

namespace App\Common\Database\Primary;

use App\Common\Database\AbstractAppTable;
use App\Common\Exception\AppException;
use App\Common\Kernel;
use App\Common\Posts\Post;
use Comely\Database\Exception\ORM_ModelNotFoundException;
use Comely\Database\Schema\Table\Columns;
use Comely\Database\Schema\Table\Constraints;

/**
 * Class Users
 * @package App\Common\Database\Primary
 */
class Posts extends AbstractAppTable
{
    public const NAME = 'posts';
    public const MODEL = 'App\Common\Posts\Post';
    public const BINARY_OBJ_SIZE = 4096;

    /**
     * @param Columns $cols
     * @param Constraints $constraints
     */
    public function structure(Columns $cols, Constraints $constraints): void
    {
        $cols->defaults("ascii", "ascii_general_ci");

        $cols->int("id")->bytes(4)->unSigned()->autoIncrement();
        $cols->int("author")->bytes(4)->unSigned()->nullable();
       
        $cols->string("author_name")->length(255)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci");

        $cols->string("title")->length(255)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci");

        $cols->string("content")->length(512)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci");
        
        $cols->string("image_url")->length(255)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci")->nullable();        
     
        $cols->int("created_at")->bytes(4)->unSigned();
        $cols->int("updated_at")->bytes(4)->unSigned()->nullable();

        $cols->string("category")->length(255)
            ->charset("utf8mb4")->collation("utf8mb4_general_ci")->default("general");

        $cols->primaryKey("id");

        $constraints->foreignKey("author")->table(Users::NAME, "id");
    }
   

    public static function List(?string $title = null): array
    {
        $query = 'WHERE 1 ORDER BY `id` ASC';
        $queryData = null;
        if (is_string($title) && $title !="") {
            $query = 'WHERE `title`=? ORDER BY `id` ASC';
            $queryData = [$title];
        }

        try {
            return Posts::Find()->query($query, $queryData)->all();
        } catch (\Exception $e) {
            Kernel::getInstance()->errors()->trigger($e, E_USER_WARNING);
        }

        return [];
    }
}
