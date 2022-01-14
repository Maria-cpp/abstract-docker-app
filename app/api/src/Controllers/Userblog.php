<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Common\Config\ProgramConfig;
use App\Common\Database\Primary\Blogs;
use App\Common\Blogs\Blog;
use App\Common\Exception\API_Exception;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use Comely\Database\Schema;
use Comely\DataTypes\Integers;

/**
 * Class UserBlog
 * @package App\API\Controllers
 */
class Userblog extends AbstractSessionAPIController
{
    /**
     * @throws API_Exception
     */
    public function sessionAPICallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Blogs');

    }

    /**
     * @throws API_Exception
     * @throws AppException
     * @throws \Comely\Database\Exception\DatabaseException
     */
    public function get(): void
    {
        $Userblog = \App\Common\Database\Primary\Blogs::List("Abstract Docker App");

        $this->status(true);
        $this->response()->set("Blog", $Userblog);
    }

    public function post(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Users');
        Schema::Bind($db, 'App\Common\Database\Primary\Blogs');
       
        // title
         try {
            $title = trim(strval($this->input()->get("title")));
            if (!$title) {
                throw new API_Exception('TITLE_REQ');
            } elseif (!Integers::Range(strlen($title), 5, 50)) {
                throw new API_Exception('TITLE_LEN');
            } elseif (!preg_match('/^[a-z]+(\s[a-z]+)*$/i', $title)) {
                throw new API_Exception('TITLE_INVALID');
            }
        } catch (AppException $e) {
            $e->setParam("title");
            throw $e;
        }

        // content
        try {
            $content = trim(strval($this->input()->get("content")));
            if (!$content) {
                throw new API_Exception('CONTENT_REQ');
            } elseif (!Integers::Range(strlen($content), 100, 1000)) {
                throw new API_Exception('CONTENT_LEN');
            }
        } catch (AppException $e) {
            $e->setParam("content");
            throw $e;
        }

        // Author_name
        try {
            $author_name = trim(strval($this->input()->get("author_name")));
            if (!$author_name) {
                $author_name="Zeenat Usman";
            }elseif (strlen($author_name) > 20) {
                throw new API_Exception('AUTHOR_LEN');
            }elseif (!preg_match('/^[a-z]+(\s[a-z]+)*$/i', $author_name)) {
                throw new API_Exception('AUTHOR_NAME_INVALID');
            }
        } catch (AppException $e) {
            $e->setParam("author_name");
            throw $e;
        }

        // category
        try {
            $category = trim(strval($this->input()->get("category")));
            if (!$category) {
                $category="general";
            }  elseif (strlen($category) > 50) {
                throw new API_Exception('CATEGORY_LEN');
            }
        } catch (AppException $e) {
            $e->setParam("category");
            throw $e;
        }

        // Insert Blog?
        try {
            $db->beginTransaction();
            $blog = new Blog();
            $blog->id = 0;
            $blog->author_name = $author_name;
            $blog->title = $title;
            $blog->content = $content;
            // $blog->image_url="src/";
            $blog->created_at = time();
            $blog->updated_at=time();
            $blog->category = $category;

            $blog->query()->insert(function () {
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
        $this->response()->set("Blog", $blog);
    }
}
