<?php
declare(strict_types=1);

namespace App\Common\Category;

use App\Common\Database\AbstractAppModel;
use App\Common\Database\Primary\PostsCategory;

/**
 * Class Category
 * @package App\Common\Category
 */
class Category extends AbstractAppModel
{
    public const TABLE = PostsCategory::NAME;

    /** @var int */
    public int $id;    
    /** @var string */
    public string $name;

}
