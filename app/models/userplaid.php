<?php

/**
 * Class UserToken
 *
 * MAGIC METHODS
 * @method User getUser()
 */
class UserPlaid extends BaseModel
{
    public $user_plaid_id;
    public $user_id;
    public $token;
    public $iv;
    public $next_cursor;

    protected static $_tableName = 'user_plaid';
    protected static $_primaryKey = 'user_plaid_id';
    protected static $_relations = [];

    protected static $_tableFields = [
        'user_id',
        'token',
        'iv',
        'next_cursor',
    ];

    protected static function defineRelations()
    {
        self::addRelationOneToOne('user_id', 'User', 'user_id');
    }

}
