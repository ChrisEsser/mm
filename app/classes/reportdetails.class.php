<?php

class ReportDetails
{
    public $reporting_on = '';
    public $graph_type = '';
    public $series = '';
    public $length = '';

    /** @var ReportInclude[] array */
    public $include = [];
    public $exclude = [];

    public function __construct($rawString)
    {
        if (!empty($rawString)) {
            $decodedJson = json_decode(base64_decode($rawString), true);
        }

        foreach ($decodedJson as $key => $value) {
            if ($key == 'include') {
                foreach ($value as $inc) {
                    $include = new ReportInclude($inc);
                    $this->include[] = $include;
                }
            } else {
                $this->$key = $value;
            }
        }
    }
}