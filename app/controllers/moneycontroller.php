<?php

class MoneyController extends BaseController
{
    public function beforeAction()
    {
        if (empty(Auth::loggedInUser())) {
            throw new Exception404();
        }
    }

    public function transactions()
    {

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

        do {

            $response = $plaid->transactions->sync($decryptedToken, $cursor, null, ['include_personal_finance_category' => true]);
            $hasMore = (bool)$response->has_more;
            $cursor = $response->next_cursor;

            foreach ($response->added as $new) {

                // create categories that do not already exist
                $category = Category::findOne(['detail_desc' => $new->personal_finance_category->detailed]);
                if (!$category->category_id) {
                    $category = new Category();
                    $category->primary_desc = $new->personal_finance_category->primary;
                    $category->detail_desc = $new->personal_finance_category->detailed;
                    $category->text_desc = '';
                    $category->save();
                }
                $categoryId = (int)$category->category_id;

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

                $category = Category::findOne(['detail_desc' => $new->personal_finance_category->detailed]);
                if (!$category->category_id) {
                    $category = new Category();
                    $category->primary_desc = $new->personal_finance_category->primary;
                    $category->detail_desc = $new->personal_finance_category->detailed;
                    $category->text_desc = '';
                    $category->save();
                }
                $categoryId = (int)$category->category_id;

                $transaction = Transaction::findOne(['plaid_id' => $modified->transaction_id]);
                $transaction->title = $new->name;
                $transaction->merchant = $new->merchant_name;
                $transaction->amount = $new->amount;
                $transaction->date = $new->date;
                $transaction->category_id = $categoryId;
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

    public function reports()
    {
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/chart.js');
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/apexcharts');
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