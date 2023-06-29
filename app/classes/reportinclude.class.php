<?php

class ReportInclude
{
    public $alias = '';
    public $list = [];

    public function __construct($include)
    {
        $include = (object)$include;
        $this->alias = $include->alias;
        $this->list = $include->list;
    }
}