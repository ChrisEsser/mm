<?php

class MoneyController extends BaseController
{
    private $similarTitleThreshold = 90;

    public function beforeAction()
    {
        if (empty(Auth::loggedInUser())) {
            throw new Exception404();
        }
    }

    public function transactions()
    {

    }

    public function editTransaction($params)
    {
        $transactionId = $params['transactionId'] ?? 0;
        $transaction = ($transactionId)
            ? Transaction::findOne(['transaction_id' => $transactionId])
            : new Transaction();

        $categories = Category::find([], ['primary_desc' => 'ASC', 'detail_desc' => 'ASC']);

        $this->view->setVar('transaction', $transaction);
        $this->view->setVar('categories', $categories);
    }

    public function saveTransaction()
    {
        $this->render = false;

        $transactionId = $_POST['transaction'] ?? 0;
        $transaction = ($transactionId)
            ? Transaction::findOne(['transaction_id' => $transactionId])
            : new Transaction();

        $transaction->title = $_POST['title'];
        $transaction->merchant = $_POST['merchant'];
        $transaction->amount = floatval($_POST['amount']);
        $transaction->category_id = intval($_POST['category_id']);
        $transaction->save();

        if (!empty($_POST['copy_merchant'])) {

            // update all other transactions with this merchant to also be this category
            Transaction::find(['merchant' => $_POST['merchant'], 'category_id' => 0])
                ->update(['category_id' => $_POST['category_id']]);
            // save a link in the matrix for future transactions with merchant to default to this category
            $matrix = new CategoryMatrix();
            $matrix->category_id = $_POST['category_id'];
            $matrix->merchant = $_POST['merchant'];
            try {
                $matrix->save();
            } catch(Exception $e) {
                // there is a unique constraint on this table. likely just means this already exists
                // TODO: update this so it's not in try catch. hard to catch actual issues this way
            }
        }

        if (!empty($_POST['copy_title'])) {

            // save a link in the matrix for future transactions with merchant to default to this category
            $matrix = new CategoryMatrix();
            $matrix->category_id = $_POST['category_id'];
            $matrix->title = $_POST['title'];
            try {
                $matrix->save();
            } catch(Exception $e) {
                // there is a unique constraint on this table. likely just means this already exists
                // TODO: update this so it's not in try catch. hard to catch actual issues this way
            }

            // now I want to update all transactions where the title is 90% or similar to this title
            // I want to also only update one where the merchant matches so I don't update all where title is empty
            $db = new StandardQuery();
            $transactions = $db->rows('SELECT transaction_id, title FROM transactions');

            $toUpdate = [];
            foreach ($transactions as $trans) {

                // strip number characters... lets only compare alpha
                $title1 = $this->removeNumbers($trans->title);
                $title2 = $this->removeNumbers($_POST['title']);

                $similarity = similar_text($title1, $title2, $percent);
                if ($percent >= $this->similarTitleThreshold) {
                    $toUpdate[] = $trans->transaction_id;
                }
            }

            if (!empty($toUpdate)) {
                $sql = 'UPDATE transactions SET category_id = ' . $_POST['category_id'] . ' WHERE category_id = 0 AND transaction_id IN (' . implode(',', $toUpdate) . ') ';
                $db->run($sql);
            }

        }


        HTTP::redirect('/money/transactions');
    }

    public function sync()
    {
        $this->render = false;
        HTTP::removePageFromHistory();

        $loggedInUser = Auth::loggedInUser();

        /** @var UserPlaid $userPlaid */
        $userPlaid = UserPlaid::findOne(['user_id' => $loggedInUser]);
        $encryptedToken = base64_decode($userPlaid->token);
        $iv = base64_decode($userPlaid->iv);
        $decryptedToken = openssl_decrypt($encryptedToken, 'AES-256-CBC', $_ENV['ENCRYPT_KEY'], 0, $iv);

        $plaid = new TomorrowIdeas\Plaid\Plaid($_ENV['PLAID_CLIENT_ID'], $_ENV['PLAID_SECRET'], $_ENV['PLAID_ENV']);

        $cursor = $userPlaid->next_cursor;

        $categoryMatrix = CategoryMatrix::find();

        do {

            $response = $plaid->transactions->sync($decryptedToken, $cursor, null, ['include_personal_finance_category' => true]);
            $hasMore = (bool)$response->has_more;
            $cursor = $response->next_cursor;

            foreach ($response->added as $new) {

                $categoryId = 0;
                foreach ($categoryMatrix as $category) {
                    if ($category->merchant == $new->merchant_name) {
                        $categoryId = $category->category_id;
                        break;
                    } else {
                        $title1 = $this->removeNumbers($category->title);
                        $title2 = $this->removeNumbers($new->name);
                        $similarity = similar_text($title1, $title2, $percent);
                        if ($percent >= $this->similarTitleThreshold) {
                            $categoryId = $category->category_id;
                            break;
                        }
                    }
                }

                $transaction = new Transaction();
                $transaction->user_id = $loggedInUser;
                $transaction->plaid_id = $new->transaction_id;
                $transaction->title = $new->name;
                $transaction->merchant = $new->merchant_name;
                $transaction->amount = $new->amount;
                $transaction->date = $new->date;
                $transaction->category_id = $categoryId;
                $transaction->save();

            }

            foreach ($response->modified as $modified) {

                $transaction = Transaction::findOne(['plaid_id' => $modified->transaction_id]);
                $transaction->title = $new->name;
                $transaction->merchant = $new->merchant_name;
                $transaction->amount = $new->amount;
                $transaction->date = $new->date;
                $transaction->save();

            }

            foreach ($response->removed as $remove) {

                $transaction = Transaction::findOne(['plaid_id' => $remove->transaction_id])->delete();

            }

        } while ($hasMore);

        if ($cursor) {
            $userPlaid->next_cursor = $cursor;
            $userPlaid->save();
        }

        // now update the current balance record
        $response = $plaid->accounts->getBalance($decryptedToken);
        $totalBalance = 0;

        foreach ($response->accounts as $account) {
            $totalBalance += $account->balances->current;
        }

        $db = new StandardQuery();

        $sql = 'SELECT amount FROM user_balances WHERE user_id = ' . $loggedInUser . ' ORDER BY date DESC LIMIT 1';

        $currentBalance = 0;
        foreach ($db->rows($sql) as $row) {
            $currentBalance = $row->amount;
            break;
        }

        if ($currentBalance != $totalBalance) {
            $sql = 'INSERT INTO user_balances (user_id, amount, date) 
                    VALUES (' . $loggedInUser . ', ' . $totalBalance . ', CURRENT_TIMESTAMP)';
            $db->run($sql);
        }

        HTTP::redirect('/money/transactions');

    }

    public function categories()
    {

    }

    public function editCategory($params)
    {
        $categoryId = $params['categoryId'] ?? 0;
        $category = ($categoryId)
            ? Category::findOne(['category_id' => $categoryId])
            : new Category();

        $this->view->setVar('category', $category);
    }

    public function saveCategory()
    {
        $this->render = false;

        $categoryId = $_POST['category'] ?? 0;
        /** @var Category $category */
        $category = ($categoryId)
            ? Category::findOne(['category_id' => $categoryId])
            : new Category();

        $category->primary_desc = $_POST['primary_desc'];
        $category->detail_desc = $_POST['detail_desc'];
        $category->save();

        HTTP::redirect('/money/categories');
    }

    public function reports()
    {
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/chart.js');
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/apexcharts');
    }

    public function reports2()
    {
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/chart.js');
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/apexcharts');

        $reports = Report::find(['user_id' => Auth::loggedInUser()], ['sort_order' => 'ASC']);

        $this->view->setVar('reports', $reports);
    }

    public function reportDetail()
    {
        $title = $_GET['title'] ?? '';
        $merchant = $_GET['merchant'] ?? '';
        $categoryId = $_GET['category'] ?? '';

        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/chart.js');
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/apexcharts');

        $this->view->setVar('title', $title);
        $this->view->setVar('merchant', $merchant);
        $this->view->setVar('categoryId', $categoryId);
    }

    public function settings()
    {
        $loggedInUser = Auth::loggedInUser();
        $userPlaid = UserPlaid::findOne(['user_id' => $loggedInUser]);
        $hasConnection = ($userPlaid->user_plaid_id) ? true : false;

        $this->view->setVar('hasConnection', $hasConnection);
    }

    public function createLinkToken()
    {
        $this->render = false;

        $userId = Auth::loggedInUser();
        $plaidUser = new TomorrowIdeas\Plaid\Entities\User($userId);
        $plaid = new TomorrowIdeas\Plaid\Plaid($_ENV['PLAID_CLIENT_ID'], $_ENV['PLAID_SECRET'], $_ENV['PLAID_ENV']);
        $response = $plaid->tokens->create(
            'Plaid Test App',
            'en',
            ['US'],
            $plaidUser,
            ['transactions']
        );

        echo json_encode([
            'expiration' => $response->expiration,
            'link_token' => $response->link_token,
            'request_id' => $response->request_id,
        ]);
        exit;
    }

    public function exchangeLinkToken()
    {
        $this->render = false;

        $userId = Auth::loggedInUser();
        $token = $_GET['public_token'] ?? '';
        if (!$token) {
            throw new Exception('Invalid Public Token');
        }

        $plaid = new TomorrowIdeas\Plaid\Plaid($_ENV['PLAID_CLIENT_ID'], $_ENV['PLAID_SECRET'], $_ENV['PLAID_ENV']);
        $response = $plaid->items->exchangeToken($token);
        $accessToken = $response->access_token;
        if (!$accessToken) {
            throw new Exception('Invalid Access Token Request');
        }

        $iv = openssl_random_pseudo_bytes(16);

        // Encrypt the access token
        $encryptedToken = openssl_encrypt($accessToken, 'AES-256-CBC', $_ENV['ENCRYPT_KEY'], 0, $iv);

        $encryptedToken = base64_encode($encryptedToken);
        $iv = base64_encode($iv);

        // Store $encryptedToken in your database
        $userPlaid = UserPlaid::findOne(['user_id' => $userId]);
        if (!$userPlaid) {
            $userPlaid = new UserPlaid();
        }
        $userPlaid->user_id = $userId;
        $userPlaid->token = $encryptedToken;
        $userPlaid->iv = $iv;
        $userPlaid->save();

        echo 'success';
        exit;
    }

    public function plaidCallback()
    {
        $this->render = false;

    }

    private function getPlaidAccessToken()
    {
        $loggedInUser = Auth::loggedInUser();
        $userPlaid = UserPlaid::findOne(['user_id' => $loggedInUser]);
        $encryptedToken = base64_decode($userPlaid->token);
        $iv = base64_decode($userPlaid->iv);
        return openssl_decrypt($encryptedToken, 'AES-256-CBC', $_ENV['ENCRYPT_KEY'], 0, $iv);
    }



    private function removeNumbers($string)
    {
        return preg_replace('/[0-9]/', '', $string);
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