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
            }

            $data = [];
            $data['categorySpending'] = $this->getCategorySpending($start, $end, $group);
            $data['categorySpendingGrouped'] = $this->getCategorySpendingGrouped($start, $end, $group, $mode);
            $data['expenseRevenue'] = $this->getExpenseRevenueData($start, $end, $mode);
            $data['balance'] = $this->getBalance();

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

    private function getBalance()
    {
        $db = new StandardQuery();
        $loggedInUser = Auth::loggedInUser();

        $sql = 'SELECT amount, date 
                FROM user_balances 
                WHERE user_id = ' . $loggedInUser . '
                ORDER BY date DESC 
                LIMIT 1';

        $result = $db->rows($sql);

        $return = [
            'date' => 'never',
            'amount' => 0,
        ];
        foreach ($result as $row) {
            $return = [
                'date' => $row->date,
                'amount' => $row->amount,
            ];
            break;
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
                WHERE c.primary_desc NOT LIKE \'%INCOME%\' AND t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                    AND c.primary_desc NOT LIKE \'%ACCOUNT_TRANSFER%\' 
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
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\' AND c.primary_desc NOT LIKE \'%INCOME%\'
                        AND c.primary_desc NOT LIKE \'%ACCOUNT_TRANSFER%\' 
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
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\' AND c.primary_desc NOT LIKE \'%INCOME%\'
                        AND c.primary_desc NOT LIKE \'%ACCOUNT_TRANSFER%\' 
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
                        SUM(CASE WHEN c.primary_desc LIKE \'%INCOME%\' THEN t.amount ELSE 0 END) AS income_amount,
                        SUM(CASE WHEN c.primary_desc NOT LIKE \'%INCOME%\' THEN t.amount ELSE 0 END) AS expense_amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                        AND c.primary_desc NOT LIKE \'%ACCOUNT_TRANSFER%\' 
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
                        SUM(CASE WHEN c.primary_desc LIKE \'%INCOME%\' THEN t.amount ELSE 0 END) AS income_amount,
                        SUM(CASE WHEN c.primary_desc NOT LIKE \'%INCOME%\' THEN t.amount ELSE 0 END) AS expense_amount
                    FROM transactions t
                    INNER JOIN categories c ON t.category_id = c.category_id
                    WHERE t.date >= \'' . $start . '\' AND t.date <= \'' . $end . '\'
                        AND c.primary_desc NOT LIKE \'%ACCOUNT_TRANSFER%\' 
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
