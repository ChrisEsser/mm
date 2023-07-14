<?php

class IndexController extends BaseController
{
    public function beforeAction()
    {

    }

    public function index()
    {
        $this->render = false;

        if (Auth::loggedInUser()) {
            HTTP::redirect('/money/reports');
        } else {
            HTTP::redirect('/login');
        }

    }

    public function test()
    {
        if (!Auth::loggedInUser()) throw new Exception404();

        HTML::addScriptToHead('https://d3js.org/d3.v7.min.js');
        HTML::addScriptToHead('/js/chrischart.js?ver=4');
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