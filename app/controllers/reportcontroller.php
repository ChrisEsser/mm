<?php

class ReportController extends BaseController
{

    public function beforeAction()
    {
        if (empty(Auth::loggedInUser())) {
            throw new Exception404();
        }

        $this->render = false;
        HTTP::removePageFromHistory();
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

        try {

            $db = new StandardQuery();

            $end = date('Y-m-t'); // Current date
            $start = date('Y-m-01', strtotime('-1 year', strtotime($end)));

            if ($merchant) {

                $sql = 'select \'\' as `month`, sum(amount) as `sum`, avg(amount) as `average`, count(amount) as `count` 
                        from transactions 
                        where `date` >= \'' . $start . '\' and `date` <= \'' . $end . '\' and merchant = \'' . $merchant . '\'';

                $totals = $db->rows($sql);

                $sql = 'SELECT t1.month AS `month`,
                            SUM(t2.amount) AS `sum`,
                            AVG(t2.amount) AS `average`,                
                            COUNT(t2.amount) AS `count`
                    FROM (
                        SELECT DATE_FORMAT(date, \'%Y-%m\') AS month
                        FROM transactions
                        WHERE date >= \'' . $start . '\' AND date <= \'' . $end . '\' AND merchant = \'' . $merchant . '\'
                        GROUP BY month
                    ) AS t1
                    INNER JOIN transactions AS t2 ON DATE_FORMAT(t2.date, \'%Y-%m\') <= t1.month AND merchat = \'' . $merchant . '\'
                    GROUP BY t1.month
                    ORDER BY t1.month';

                $series = $db->rows($sql);

                $data = $totals + $series;

            } else if ($title) {

                $sql = 'SELECT t1.month,
                           AVG(t2.amount) AS average,
                           SUM(t2.amount) AS `sum`,
                           COUNT(t2.amount) AS `count`
                    FROM (
                        SELECT DATE_FORMAT(date, \'%Y-%m\') AS month
                        FROM transactions
                        WHERE date BETWEEN \'' . $start . '\' AND \'' . $end . '\' AND title = \'' . $title . '\'
                        GROUP BY month WITH ROLLUP
                    ) AS t1
                    INNER JOIN transactions AS t2 ON DATE_FORMAT(t2.date, \'%Y-%m\') <= t1.month
                    GROUP BY t1.month WITH ROLLUP
                    ORDER BY t1.month';

                $data = $db->rows($sql);

            } else throw new Exception('Invalid request type');

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

}
