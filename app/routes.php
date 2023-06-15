<?php

return [

    ['GET', '/', 'IndexController#index'],
    ['GET', '', 'IndexController#index'],

    ['GET', '/money/transactions', 'MoneyController#transactions'],
    ['GET', '/money/transactions/create', 'MoneyController#editTransaction'],
    ['GET', '/money/transactions/edit/[i:transactionId]', 'MoneyController#editTransaction'],
    ['POST', '/money/transactions/save', 'MoneyController#saveTransaction'],

    ['GET', '/money/categories', 'MoneyController#categories'],
    ['GET', '/money/categories/create', 'MoneyController#editCategory'],
    ['GET', '/money/categories/edit/[i:categoryId]', 'MoneyController#editCategory'],
    ['POST', '/money/categories/save', 'MoneyController#saveCategory'],

    ['GET', '/money/sync', 'MoneyController#sync'],


    ['GET', '/money/reports', 'MoneyController#reports'],

    ['GET', '/money/settings', 'MoneyController#settings'],
    ['POST', '/money/settings/createLinkToken', 'MoneyController#createLinkToken'],
    ['POST', '/money/settings/exchangeLinkToken', 'MoneyController#exchangeLinkToken'],

    ['GET', '/users', 'UserController#users'],
    ['GET', '/users/edit/[i:userId]', 'UserController#edit'],
    ['GET', '/users/create', 'UserController#edit'],

    ['GET', '/login', 'LoginController#login'],
    ['POST', '/process-login', 'LoginController#process'],
    ['GET', '/logout', 'LoginController#logout'],

    ['POST', '/app-data/users', 'TableDataController#users'],
    ['POST', '/app-data/categories', 'TableDataController#categories'],
    ['POST', '/app-data/transactions', 'TableDataController#transactions'],

    ['GET', '/reports/get-data', 'ReportController#getData'],

];