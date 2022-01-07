<?php
declare(strict_types=1);

namespace App\Common;

use App\Common\Database\AbstractAppModel;
use App\Common\Database\Primary\Posts;
use App\Common\Exception\AppException;

/**
 * Class Post
 * @package App\Common
 */
class Post extends AbstractAppModel
{
    public const TABLE = \App\Common\Database\Primary\Posts::NAME;
    // public const SERIALIZABLE = true;

    /** @var int */
    public int $id;
    /** @var string */
    public string $title;
    /** @var string */
    public string $content;
    /** @var string */
    public string $author_name;
    /** @var string */
    public string $image_url;
     /** @var string */
    public string $category;
     /** @var int */
    public int $created_at;
     /** @var int */
     public int $updated_at;

    /**
     * @throws AppException
     */
    // public function beforeQuery()
    // {
        
    // }
    
}
