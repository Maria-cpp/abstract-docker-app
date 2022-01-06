<?php
declare(strict_types=1);

namespace App\Common\Posts;

use App\Common\Database\AbstractAppModel;
use App\Common\Database\Primary\Posts;
use App\Common\Exception\AppConfigException;
use App\Common\Exception\AppException;
use App\Common\Validator;
use Comely\Cache\Exception\CacheException;
use Comely\DataTypes\Buffer\Binary;

/**
 * Class User
 * @package App\Common\Users
 */
class Post extends AbstractAppModel
{
    public const TABLE = Posts::NAME;
    public const SERIALIZABLE = true;


    /** @var int */
    public int $id;
    /** @var string */
    public string $title;
    /** @var string */
    public string $content;
     /** @var int */
     public int $author;
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
    public function beforeQuery()
    {
      parent::onSerialize();   
    }
    
}
