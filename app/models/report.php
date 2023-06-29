<?php

/**
 * Class Report
 *
 * MAGIC METHODS
 * @method User[] getUser()
 */
class Report extends BaseModel
{
    public $report_id;
    public $user_id;
    public $type;
    public $title;
    public $sort_order;
    public $size;
    public $details;

    protected static $_tableName = 'user_reports';
    protected static $_primaryKey = 'report_id';
    protected static $_relations = [];

    private $cache = [];

    protected static $_tableFields = [
        'user_id',
        'type',
        'title',
        'sort_order',
        'size',
        'details',
    ];

    public function getDetails()
    {
        if (isset($this->cache['details'])) {
            return $this->cache['details'];
        }
        $details = new ReportDetails($this->details);
        $this->cache['details'] = $details;

        return $details;
    }

    protected static function defineRelations()
    {
        self::addRelationOneToMany('user_id', 'User', 'user_id');
    }

}