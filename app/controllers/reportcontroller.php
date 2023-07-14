<?php

class ReportController extends BaseController
{

    public function beforeAction()
    {
        if (empty(Auth::loggedInUser())) {
            throw new Exception404();
        }

        HTTP::removePageFromHistory();
    }

    public function getCustomData($params)
    {
        $this->render = false;

        $return = [
            'result' => '',
            'data' => [],
        ];

        $reportId = $params['reportId'] ?? 0;
        if (!$reportId) throw new Exception404();

        try {

            /** @var Report $report */
            $report = Report::findOne(['report_id' => $reportId]);

            $end = date('Y-m-d'); // Current date
            if ($report->getDetails()->length == '7d') {
                $start = date('Y-m-d', strtotime('-7 day', strtotime($end)));
            } else if($report->getDetails()->length == '1m') {
                $start = date('Y-m-d', strtotime('-1 month', strtotime($end)));
            } else if($report->getDetails()->length == '1q') {
                $start = date('Y-m-d', strtotime('-3 month', strtotime($end)));
            } else if($report->getDetails()->length == '1y') {
                $start = date('Y-m-d', strtotime('-1 year', strtotime($end)));
            } else if($report->getDetails()->length == '2y') {
                $start = date('Y-m-d', strtotime('-2 year', strtotime($end)));
            } else if($report->getDetails()->length == '3y') {
                $start = date('Y-m-d', strtotime('-3 year', strtotime($end)));
            } else if($report->getDetails()->length == 'all') {
                $start = date('Y-m-d', strtotime('-100 year', strtotime($end)));
            } else throw new Exception('Invalid length');

            if (!in_array($report->getDetails()->series, ['day', 'month', 'quarter', 'year'])) {
                throw new Exception('Invalid series grouping');
            }

            $includes = [];
            foreach ($report->getDetails()->include as $include) {
                foreach ($include->list as $item) {
                    $includes[] = $item;
                }
            }

            $db = new StandardQuery();

            if (in_array($report->getDetails()->reporting_on, ['merchant', 'title'])) {

                $field = ($report->getDetails()->reporting_on == 'merchant') ? 'merchant' : 'title';

                // merchant or title report
                $sql = 'SELECT amount, `date`, ' . $field . ' AS field
                        FROM transactions 
                        WHERE user_id = ' . Auth::loggedInUser() . ' 
                            AND `date` >= \'' . $start . '\' AND `date` <= \'' . $end . '\'
                            AND ' . $report->getDetails()->reporting_on . ' IN (';

                $sep = '';
                for($i = 0; $i < count($includes); $i++) {
                    $sql .= $sep . '?';
                    $sep = ', ';
                }

                $sql .= ')';

                $transactions = $db->rows($sql, $includes);

            } else {

                $field = ($report->getDetails()->reporting_on == 'category_primary') ? 'primary_desc' : 'detail_desc';

                // category report
                $sql = 'SELECT t.amount, t.`date`, c.' . $field . ' AS `field`
                        FROM transactions t 
                        INNER JOIN categories c ON c.category_id = t.category_id
                        WHERE t.user_id = ' . Auth::loggedInUser() . '
                            AND `date` >= \'' . $start . '\' AND `date` <= \'' . $end . '\'
                            AND c.' . $field . ' IN (';

                $sep = '';
                for($i = 0; $i < count($includes); $i++) {
                    $sql .= $sep . '?';
                    $sep = ', ';
                }

                $sql .= ')';

                $transactions = $db->rows($sql, $includes);

            }

            $finalData = [];

            if ($report->getDetails()->graph_type == 'pie') {

                // pie graphs are a little different they are just the sum of each group

                foreach ($report->getDetails()->include as $include) {

                    $groupTotal = 0;

                    foreach ($transactions as $transaction) {
                        if (in_array($transaction->field, $include->list)) {
                            $groupTotal += $transaction->amount;
                        }
                    }

                    $finalData[] = [
                        'name' => $include->alias,
                        'data' => $groupTotal
                    ];

                }

            } else {

                // line and bar graphs have grouped data that are grouped by time chunks

                foreach ($report->getDetails()->include as $include) {

                    $groupTotal = 0;
                    $tmpGroupTotals = [];

                    if ($report->getDetails()->series == 'day') {
                        $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1D'), new DateTime($end));
                        foreach ($dateRange as $date) {
                            $key = $date->format('m/d/Y');
                            $tmpGroupTotals[$key] = 0;
                        }
                    } else if ($report->getDetails()->series == 'month') {
                        $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1D'), new DateTime($end));
                        foreach ($dateRange as $date) {
                            $key = $date->format('m/Y');
                            $tmpGroupTotals[$key] = 0;
                        }
                    } else if ($report->getDetails()->series == 'quarter') {
                        $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P3M'), new DateTime($end));
                        foreach ($dateRange as $date) {
                            $key = 'Q' . ceil($date->format('n') / 3) . ' ' . $date->format('Y');
                            $tmpGroupTotals[$key] = 0;
                        }
                    } else if ($report->getDetails()->series == 'year') {
                        $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1Y'), new DateTime($end));
                        foreach ($dateRange as $date) {
                            $key = $date->format('Y');
                            $tmpGroupTotals[$key] = 0;
                        }
                    }

                    foreach ($transactions as $transaction) {

                        if (in_array($transaction->field, $include->list)) {

                            if ($report->getDetails()->series == 'day') {
                                $date = date('m/d/Y', strtotime($transaction->date));
                            } else if ($report->getDetails()->series == 'month') {
                                $date = date('m/Y', strtotime($transaction->date));
                            } else if ($report->getDetails()->series == 'quarter') {
                                $tmpDate = new DateTime($transaction->date);
                                $quarter = ceil($tmpDate->format('n') / 3);
                                $year = $tmpDate->format('Y');
                                $date = 'Q' . $quarter . ' ' . $year;
                            } else if ($report->getDetails()->series == 'year') {
                                $date = date('Y', strtotime($transaction->date));
                            }

                            $tmpGroupTotals[$date] += $transaction->amount;

                        }

                    }

                    $finalData[] = [
                        'name' => $include->alias,
                        'data' => $tmpGroupTotals,
                    ];

                }

            }

            $return = [
                'result' => 'success',
                'data' => $finalData,
            ];

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'error_message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;
    }

    public function getData()
    {
        $this->render = false;

        $return = [
            'result' => '',
            'data' => [],
        ];

        try {

            $group = $_GET['group'] ?? 'primary';
            $mode = $_GET['mode'] ?? 'year';
            $year = $_GET['year'] ?? date('Y');

            if ($mode == 'month') {
                $period = $_GET['period'] ?? date('n');
                $start = date('Y-m-d', strtotime("$year-$period-01"));
                $end = date('Y-m-t', strtotime("$year-$period-01"));
            } else if ($mode == 'year') {
                $start = date('Y-01-01', strtotime("$year-01-01"));
                $end = date('Y-12-t', strtotime("$year-01-01"));
            } else if ($mode == 'quarter') {
                $period = $_GET['period'] ?? ceil(date('n') / 3);
                $firstMonth = (($period - 1) * 3) + 1;
                $lastMonth = $firstMonth + 2;
                $start = date('Y-m-d', strtotime("$year-$firstMonth-01"));
                $end = date('Y-m-t', strtotime("$year-$lastMonth-01"));
            } else if ($mode == 'all') {
                $start = date('Y-01-01', 0);
                $end = date('Y-m-d', time());
            }

            $data = [];
            $data['categorySpending'] = $this->getCategorySpending($start, $end, $group);
            $data['categorySpendingGrouped'] = $this->getCategorySpendingGrouped($start, $end, $group, $mode);
            $data['expenseRevenue'] = $this->getExpenseRevenueData($start, $end, $mode);
            $data['balance'] = $this->getBalance($start, $end, $mode);

            $return = [
                'result' => 'success',
                'data' => $data,
            ];

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'error_message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;

    }

    public function getDetailData()
    {
        $this->render = false;

        $return = [
            'result' => '',
            'data' => [],
        ];

        $title = $_GET['title'] ?? '';
        $merchant = $_GET['merchant'] ?? '';
        $categoryId = $_GET['category'] ?? 0;

        try {

            $db = new StandardQuery();

            $end = date('Y-m-t'); // Current date
            $start = date('Y-m-01', strtotime('-1 year', strtotime($end)));

            $data = [];

            if ($merchant) {

                $sql = 'select \'\' as `month`, sum(amount) as `sum`, avg(amount) as `average`, count(amount) as `count` 
                        from transactions 
                        where `date` >= \'' . $start . '\' and `date` <= \'' . $end . '\' and merchant = ?';

                $totals = $db->rows($sql, [$merchant]);

                $sql = 'select count(amount) as `count`, sum(amount) as `sum`, avg(amount) as `average`, DATE_FORMAT(date, \'%Y-%m\') AS month 
                        from transactions 
                        where merchant = ? 
                            and date >= \'' . $start . '\' AND date <= \'' . $end . '\' group by DATE_FORMAT(date, \'%Y-%m\')
                        order by DATE_FORMAT(date, \'%Y-%m\')';

                $series = $db->rows($sql, [$merchant]);

                $data = $totals + $series;

            } else if ($title) {

                $sql = 'select \'\' as `month`, sum(amount) as `sum`, avg(amount) as `average`, count(amount) as `count` 
                        from transactions 
                        where `date` >= \'' . $start . '\' and `date` <= \'' . $end . '\' and title = ?';

                $totals = $db->rows($sql, [$title]);

                $sql = 'select count(amount) as `count`, sum(amount) as `sum`, avg(amount) as `average`, DATE_FORMAT(date, \'%Y-%m\') AS month 
                        from transactions 
                        where title = ? 
                            and date >= \'' . $start . '\' AND date <= \'' . $end . '\' group by DATE_FORMAT(date, \'%Y-%m\')
                        order by DATE_FORMAT(date, \'%Y-%m\')';

                $series = $db->rows($sql, [$title]);

                $data = $totals + $series;

            } else if ($categoryId) {

                $sql = 'select \'\' as `month`, sum(amount) as `sum`, avg(amount) as `average`, count(amount) as `count` 
                        from transactions 
                        where `date` >= \'' . $start . '\' and `date` <= \'' . $end . '\' and category_id = ?';

                $totals = $db->rows($sql, [$categoryId]);

                $sql = 'select count(amount) as `count`, sum(amount) as `sum`, avg(amount) as `average`, DATE_FORMAT(date, \'%Y-%m\') AS month 
                        from transactions 
                        where category_id = ? 
                            and date >= \'' . $start . '\' AND date <= \'' . $end . '\' group by DATE_FORMAT(date, \'%Y-%m\')
                        order by DATE_FORMAT(date, \'%Y-%m\')';

                $series = $db->rows($sql, [$categoryId]);

                $data = $totals + $series;

            }

            $return = [
                'result' => 'success',
                'data' => $data,
            ];

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'error_message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
        exit;
    }

    public function manage()
    {

    }

    public function edit($params)
    {
        $reportId = $params['reportId'] ?? 0;
        $report = ($reportId)
            ? Report::findOne(['report_id' => $reportId])
            : new Report();

        $countReports = Report::find(['user_id' => Auth::loggedInUser()])->count();

        $this->view->setVar('report', $report);
        $this->view->setVar('countReports', $countReports);
    }

    public function save()
    {
        $this->render = false;

        $reportId = $_POST['report'] ?? 0;
        $report = ($reportId)
            ? Report::findOne(['report_id' => $reportId])
            : new Report();

        $report->details = base64_encode(json_encode($_POST['details']));
        $report->user_id = Auth::loggedInUser();
        $report->sort_order = $_POST['sort_order'];
        $report->size = $_POST['size'];
        $report->title = $_POST['title'];

        $report->save();

        HTTP::redirect('/reports/manage');
    }

    public function delete($parames)
    {
        $this->render = false;

        $reportId = $parames['reportId'] ?? 0;
        if (!$reportId) throw new Exception404();

        Report::findOne(['report_id' => $reportId])->delete();

        HTTP::redirect('/reports/manage');
    }

    public function sort()
    {
        $this->render = false;

        $loggedInUser = intval(Auth::loggedInUser());

        if (!empty($_POST['reportList'])) {

            $db = new StandardQuery();
            $sql = 'UPDATE user_reports SET sort_order = 0 WHERE user_id <> 0 AND user_id = ' . $loggedInUser;
            $db->run($sql);

            $sql = '';
            for ($i = 1; $i <= count($_POST['reportList']); $i++) {
                $sql .= 'UPDATE user_reports SET sort_order = ' . $i . ' WHERE user_id = ' . $loggedInUser . ' AND report_id = ' . intval($_POST['reportList'][$i]) . ';' . "\n\r";
            }

            $db->run($sql);

        }

        exit();
    }

    private function getBalance($start, $end, $mode)
    {
        $db = new StandardQuery();

        $loggedInUser = Auth::loggedInUser();

        // first get the current balance regardless of start and end dates
        $sql = 'SELECT amount FROM user_balances WHERE user_id = ' . $loggedInUser . ' ORDER BY date DESC LIMIT 1';
        $r = $db->row($sql);
        $return['current_balance'] = (float)$r->amount;

        if ($mode == 'month') {

            // get all balance records for this date range
            $sql = 'SELECT DATE(date) AS day, amount
                    FROM user_balances 
                    WHERE user_id = ' . $loggedInUser . '
                        AND date >= \'' . $start . '\' AND date <= \'' . $end . '\'
                    ORDER BY day';

            $result = $db->rows($sql);

            $start = new DateTime($start);
            $end = new DateTime($end);
            $interval = new DateInterval('P1D');

            $days = [];
            $currentDate = clone $start;

            while ($currentDate <= $end) {
                $days[$currentDate->format('Y-m-d')] = 0;
                $currentDate->add($interval);
            }

            $previousValue = 0;
            foreach ($days as $day => &$value) {

                // find matching result
                $matchingResult = null;
                foreach ($result as $row) {
                    if ($row->day == $day) {
                        $matchingResult = $row;
                        break;
                    }
                }

                if ($matchingResult !== null) {
                    $previousValue = $value = $matchingResult->amount;
                } else {
                    $value = $previousValue;
                }
            }

            $return['history'] = $days;

        } else {

            // get all balance records for this date range
            $sql = 'SELECT DATE_FORMAT(date, \'%Y-%m\') AS month, amount
                    FROM user_balances 
                    WHERE user_id = ' . $loggedInUser . '
                        AND date >= \'' . $start . '\' AND date <= \'' . $end . '\'
                    ORDER BY month';

            $result = $db->rows($sql);

            $start = new DateTime($start);
            $end = new DateTime($end);
            $interval = new DateInterval('P1M');

            $months = [];
            $currentDate = clone $start;

            while ($currentDate <= $end) {
                $months[$currentDate->format('Y-m')] = 0;
                $currentDate->add($interval);
            }

            $previousValue = 0;
            foreach ($months as $month => &$value) {

                // find matching result
                $matchingResult = null;
                foreach ($result as $row) {
                    if ($row->month == $month) {
                        $matchingResult = $row;
                        break;
                    }
                }

                if ($matchingResult !== null) {
                    $previousValue = $value = $matchingResult->amount;
                } else {
                    $value = $previousValue;
                }
            }


            $return['history'] = $months;
        }

        return $return;

    }

    private function getCategorySpending($start, $end, $group)
    {
        $db = new StandardQuery();

        $groupField = 'c.' . $group . '_desc';

        $sql = 'SELECT SUM(t.amount) AS amount, ' . $groupField . ' AS description
                FROM categories c 
                INNER JOIN transactions t ON t.category_id = c.category_id
                WHERE c.primary_desc NOT LIKE \'%income%\' AND t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                    AND c.detail_desc NOT LIKE \'%account transfer%\' 
                GROUP BY ' . $groupField;

        return $db->rows($sql);
    }

    private function getCategorySpendingGrouped($start, $end, $group, $mode)
    {
        $db = new StandardQuery();

        $categoryGroupField = 'c.' . $group . '_desc';

        if ($mode == 'month') {

            // group by day
            $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1D'), new DateTime($end));

            $sql = 'SELECT DATE(t.date) AS day, ' . $categoryGroupField . ' AS label, SUM(t.amount) as amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\' AND c.primary_desc NOT LIKE \'%income%\'
                        AND c.detail_desc NOT LIKE \'%account transfer%\' 
                    GROUP BY day, ' . $categoryGroupField;

            $result = $db->rows($sql);

            $categoryDays = [];
            foreach ($result as $row) {
                if (!isset($categoryDays[$row->label])) {
                    foreach ($dateRange as $date) {
                        $day = $date->format('Y-m-d');
                        $categoryDays[$row->label][$day] = 0;
                    }
                }
                $categoryDays[$row->label][$row->day] = $row->amount;
            }

            return $categoryDays;

        } else {

            // group by month
            $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1M'), new DateTime($end));

            $sql = 'SELECT DATE_FORMAT(t.date, \'%Y-%m\') AS month, ' . $categoryGroupField . ' AS label, SUM(t.amount) AS amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\' AND c.primary_desc NOT LIKE \'%income%\'
                        AND c.detail_desc NOT LIKE \'%account transfer%\' 
                    GROUP BY month, ' . $categoryGroupField;

            $result = $db->rows($sql);

            $categoryMonths = [];
            foreach ($result as $row) {
                if (!isset($categoryMonths[$row->label])) {
                    foreach ($dateRange as $date) {
                        $month = $date->format('Y-m');
                        $categoryMonths[$row->label][$month] = 0;
                    }
                }
                $categoryMonths[$row->label][$row->month] = $row->amount;
            }

            return $categoryMonths;

        }
    }

    private function getExpenseRevenueData($start, $end, $mode)
    {
        $db = new StandardQuery();

        if ($mode == 'month') {

            // group by day
            $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1D'), new DateTime($end));
            $days = [];
            foreach ($dateRange as $date) {
                $day = $date->format('Y-m-d');
                $days[$day] = ['income' => 0, 'expense' => 0, 'profit' => 0];
            }

            $sql = 'SELECT DATE(t.date) AS day,
                        SUM(CASE WHEN c.primary_desc LIKE \'%income%\' AND c.detail_desc NOT LIKE \'%account transfer%\' THEN t.amount ELSE 0 END) AS income_amount,
                        SUM(CASE WHEN c.primary_desc NOT LIKE \'%income%\' AND c.detail_desc NOT LIKE \'%account transfer%\' THEN t.amount ELSE 0 END) AS expense_amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                    GROUP BY day';

            $result = $db->rows($sql);

            foreach ($result as $row) {
                $days[$row->day] = [
                    'income' => $row->income_amount,
                    'expense' => $row->expense_amount,
                    'profit' => $row->income_amount - $row->expense_amount,
                ];
            }

            return $days;

        } else {

            // group by month
            $dateRange = new DatePeriod(new DateTime($start), new DateInterval('P1M'), new DateTime($end));
            $months = [];
            foreach ($dateRange as $date) {
                $month = $date->format('Y-m');
                $months[$month] = ['income' => 0, 'expense' => 0, 'profit' => 0];
            }

            $sql = 'SELECT DATE_FORMAT(t.date, \'%Y-%m\') AS month, 
                        SUM(CASE WHEN c.primary_desc LIKE \'%income%\' AND c.detail_desc NOT LIKE \'%account transfer%\' THEN t.amount ELSE 0 END) AS income_amount,
                        SUM(CASE WHEN c.primary_desc NOT LIKE \'%income%\' AND c.detail_desc NOT LIKE \'%account transfer%\' THEN t.amount ELSE 0 END) AS expense_amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                    GROUP BY month';

            $result = $db->rows($sql);

            foreach ($result as $row) {
                $months[$row->month] = [
                    'income' => $row->income_amount,
                    'expense' => $row->expense_amount,
                    'profit' => $row->income_amount - $row->expense_amount,
                ];
            }

            return $months;

        }
    }

    public function afterAction()
    {
        if (!$this->render_header) {
            $layout = new AjaxLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
        else if ($this->render) {
            $layout = new AdminLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}
