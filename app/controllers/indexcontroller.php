<?php

class IndexController extends BaseController
{
    public function beforeAction()
    {

    }

    public function afterAction()
    {

    }

    public function index()
    {
        $this->render = false;

        if (Auth::loggedInUser()) {
            HTTP::redirect('/money/transactions');
        } else {
            HTTP::redirect('/login');
        }

    }

}