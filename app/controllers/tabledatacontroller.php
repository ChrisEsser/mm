<?php

class TableDataController extends BaseController
{
    protected $pageLength = 2;
    protected $page = 1;
    protected $filters = [];
    protected $sort = [];
    protected $offset = 0;

    public function beforeAction()
    {
        HTTP::removePageFromHistory();
        $this->render = false;

        if (!Auth::loggedInUser()) {
            throw new Exception404();
        }

        $requestData = json_decode($_POST['tableData']);

        $this->page = $requestData->page ?? $this->page;
        $this->pageLength = $requestData->len ?? $this->pageLength;
        $this->filters = $requestData->filter ?? $this->filters;
        $this->sort = $requestData->sort ?? $this->sort;

        $this->offset = ($this->page - 1) * $this->pageLength;
    }

    public function users()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT u.*
                FROM users u ';

        $where['deleted'] = 'u.deleted = 0';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['first_name', 'last_name', 'email', 'admin'])) {
                    $where[$col] = 'u.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'full_name') {
                    $where[$col] = '(u.first_name LIKE :' . $col . ' OR u.last_name LIKE :' . $col . ' OR CONCAT(u.first_name, \' \', u.last_name) LIKE :' . $col . ')';
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['first_name', 'last_name', 'email', 'admin'])) {
                    $order[$col] = $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function transactions()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT t.transaction_id, t.title, IFNULL(t.merchant, \'\') as merchant, t.amount, t.date, 
                       IFNULL(c.detail_desc, \'\') AS category, IFNULL(c.category_id, \'0\') AS category_id
                FROM transactions t 
                LEFT JOIN categories c ON c.category_id = t.category_id';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['title', 'merchant', 'amount', 'date'])) {
                    $where[$col] = 't.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                } else if ($col == 'category') {
                    $where[$col] = '(c.detail_desc LIKE :' . $col . ' OR c.primary_desc LIKE :' . $col . ')';
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['date', 'title', 'merchant', 'amount', 'type'])) {
                    $order[$col] = $col . ' ' . $dir;
                } else if ($col == 'category') {
                    $order[$col] =  ' c.detail_desc ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function categories()
    {
        $where = $order = [];

        $db = new StandardQuery();

        $sql = 'SELECT c.*
                FROM categories c ';

        $params = [];
        foreach ($this->filters as $filter) {
            foreach ($filter as $col => $value) {
                if (in_array($col, ['primary_desc', 'detail_desc', 'text_desc'])) {
                    $where[$col] = 'c.' . $col . ' LIKE :' . $col;
                    $params[$col] = '%' . $value . '%';
                }
            }
        }

        foreach ($this->sort as $sort) {
            foreach ($sort as $col => $dir) {
                if (in_array($col, ['primary_desc', 'detail_desc', 'text_desc'])) {
                    $order[$col] = $col . ' ' . $dir;
                }
            }
        }

        $whereString = (!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '';
        $sql .= ' ' . $whereString;

        $total = $db->count($sql, $params);
        $totalPages = ceil($total / $this->pageLength);

        $orderString = (!empty($order)) ? ' ORDER BY ' . implode(', ', $order) : '';
        $sql .= ' ' . $orderString;

        $sql .= ' LIMIT ' . $this->offset . ', ' . $this->pageLength;

        $data = $db->rows($sql, $params);

        echo json_encode([
            'total' => $total,
            'pages' => $totalPages,
            'page' => $this->page,
            'data' => $data,
        ]);
    }

    public function afterAction()
    {


    }

}