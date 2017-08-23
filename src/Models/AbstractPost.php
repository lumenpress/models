<?php

namespace Lumenpress\ORM\Models;

use Lumenpress\ORM\Builders\PostBuilder;
use Lumenpress\ORM\Models\Concerns\PostAttributes;

abstract class AbstractPost extends Model
{
    use PostAttributes;

    protected static $registeredPostTypes = [
        'post' => Post::class,
        'page' => Page::class
    ];

    const CREATED_AT = 'post_date';

    const UPDATED_AT = 'post_modified';

    protected $table = 'posts';

    protected $primaryKey = 'ID';

    protected $_slug;

    protected $dates = [
        'post_date', 
        'post_date_gmt', 
        'post_modified', 
        'post_modified_gmt'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->ID = 0;
        $this->post_title = 'Untitle';
        $this->post_parent = 0;
        $this->menu_order = 0;
        $this->post_status = 'publish';
        $this->comment_status = 'closed';
        $this->post_author = (int) lumenpress_get_current_user_id();
        $this->post_type = property_exists($this, 'postType') ? $this->postType : 'post';
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        $builder = new PostBuilder($query);
        if (property_exists($this, 'postType')) {
            $builder->where('post_type', $this->postType);
        }
        return $builder;
    }

    public function meta($key = null)
    {
        $builder = $this->hasMany(PostMeta::class, 'post_id');
        if ($key) {
            $builder->where('meta_key', $key);
        }
        return $builder;
    }

    public function save(array $options = [])
    {
        if (!$this->_slug) {
            $this->post_name = $this->post_title;
        }
        if (!parent::save($options)) {
            return false;
        }
        $this->meta->save();
        return true;
    }

    public static function registerType($type, $class)
    {
        static::$registeredPostTypes[$type] = $class;
    }

    public static function getPostClassByType($type)
    {
        return array_get(static::$registeredPostTypes, $type, Post::class);
    }
}