<?php

/**
 * Class Transaction
 *
 * MAGIC METHODS
 * @method Transaction[] getTransaction()
 */
class Category extends BaseModel
{
    public $category_id;
    public $primary_desc;
    public $detail_desc;
    public $text_desc;

    protected static $_tableName = 'categories';
    protected static $_primaryKey = 'category_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'primary_desc',
        'detail_desc',
        'text_desc',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToMany('category_id', 'Transaction', 'category_id');
    }

}