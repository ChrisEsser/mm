<?php

/**
 * Class Transaction
 *
 * MAGIC METHODS
 * @method User getUser()
 * @method Transaction getTransaction()
 */
class Transaction extends BaseModel
{
    public $transaction_id;
    public $user_id;
    public $plaid_id;
    public $title;
    public $merchant;
    public $amount;
    public $type;
    public $category_id;
    public $date;

    protected static $_tableName = 'transactions';
    protected static $_primaryKey = 'transaction_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'user_id',
        'plaid_id',
        'title',
        'merchant',
        'amount',
        'type',
        'category_id',
        'date',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
        self::addRelationOneToOne('category_id', 'Category', 'category_id');
    }

}