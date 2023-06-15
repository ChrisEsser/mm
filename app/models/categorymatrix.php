<?php

/**
 * Class CategoryMatrix
 *
 * MAGIC METHODS
 * @method Category getCategory()
 */
class CategoryMatrix extends BaseModel
{
    public $category_id;
    public $merchant;
    public $title;

    protected static $_tableName = 'category_matrix';
    protected static $_primaryKey = 'category_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'category_id',
        'merchant',
        'title',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('category_id', 'Category', 'category_id');
    }
}
