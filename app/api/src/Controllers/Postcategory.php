<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Common\Category;
use App\Common\Database\Primary\PostsCategory;
use App\Common\Exception\API_Exception;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use Comely\Database\Schema;

/**
 * Class Postcategory
 * @package App\API\Controllers
 */
class Postcategory extends AbstractSessionAPIController
{
    /**
     * @throws API_Exception
     */
    public function sessionAPICallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\PostsCategory');

    }

    /**
     * @throws API_Exception
     * @throws AppException
     * @throws \Comely\Database\Exception\DatabaseException
     */
    public function get(): void
    {
        $postcategory = PostsCategory::List("framework");

        $this->status(true);
        $this->response()->set("PostCategory", $postcategory);
    }

    public function post(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\PostsCategory');
        
        // category name
        try {
            $name = trim(strval($this->input()->get("name")));
            if (!$name) {
                $name="general";
            }  elseif (strlen($name) > 50) {
                throw new API_Exception('CATEGORY_LEN');
            }
        } catch (AppException $e) {
            $e->setParam("name");
            throw $e;
        }

        // Insert category?
        try {
            $db->beginTransaction();
            $category = new Category();
            $category->id = 0;
            $category->name = $name;

            $category->query()->insert(function () {
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
        $this->response()->set("category", $category);
    }
}
