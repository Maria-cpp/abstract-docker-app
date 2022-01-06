<?php
declare(strict_types=1);

namespace App\API\Controllers;

use App\Common\Config\ProgramConfig;
use App\Common\Database\Primary\Posts;
use App\Common\Posts\Post;
use App\Common\Exception\API_Exception;
use App\Common\Exception\AppControllerException;
use App\Common\Exception\AppException;
use App\Common\Users\User;
use Comely\Database\Schema;
use Comely\DataTypes\Integers;

class Userposts extends AbstractSessionAPIController{

    /**
     * @throws API_Exception
     */
    public function sessionAPICallback(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Posts');

    }

     /**
     * @throws API_Exception
     * @throws AppException
     * @throws \Comely\Database\Exception\DatabaseException
     */
    public function get(): void
    {
        $Posts = Posts::List("Abstract Docker App");

        $this->status(true);
        $this->response()->set("Posts", $Posts);
    }


    public function post(): void
    {
        $db = $this->app->db()->primary();
        Schema::Bind($db, 'App\Common\Database\Primary\Users');
        Schema::Bind($db, 'App\Common\Database\Primary\Posts');

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

        // Author
        try {
            $author_name = trim(strval($this->input()->get("author_name")));
            if (!$author) {
                $author="user";
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
        

        // Insert Post?
        try {
            $db->beginTransaction();
            $post = new Post();
            $post->id = 0;
            $post->title = $title;
            $post->content = $content;
            $post->category = $category;
            $post->created_at = time();
            $post->author_name = $author_name;
            $post->author = 1;
            $post->image_url="null";
            $post->updated_at=time();
            // $post->author = User::CACHE_KEY_USERNAME;

            $post->query()->insert(function () {
                throw new AppControllerException('Failed to insert post row');
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
        $this->response()->set("post", $post);
    }
}