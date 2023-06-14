<?php

/** @var $this \Template */

/** @var string $body */
$body = $this->getVar('body');
/** @var string $action */
$action = $this->getVar('action');

?>

<!DOCTYPE html>
<html>
<head>
    <title>E2 Holdings Property Manager</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css"/>
    <link rel="stylesheet" href="https://unpkg.com/filepond/dist/filepond.css"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
    <link rel="stylesheet" href="/css/style.css?ver=7"/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>
    <script src="/js/confirm.js"></script>
    <script src="/js/filepondHelper.js"></script>
    <?= HTML::displayHead() ?>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <div class="container-fluid">

        <a class="navbar-brand" href="/">E<sup>2</sup> Holdings</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
<!--                <li class="nav-item --><?//=($action == 'dashboard') ? 'active' : ''?><!--">-->
<!--                    <a class="nav-link" href="/dashboard">Dashboard</a>-->
<!--                </li>-->
                <li class="nav-item <?=($action == 'properties') ? 'active' : ''?>">
                    <a class="nav-link" href="/properties">Properties</a>
                </li>
                <li class="nav-item <?=($action == 'documents') ? 'active' : ''?>">
                    <a class="nav-link" href="/documents">Documents</a>
                </li>

                <li class="nav-item <?=($action == 'scraper') ? 'active' : ''?>">
                    <a class="nav-link" href="/scraper">Scraper</a>
                </li>
                <li class="nav-item <?=($action == 'users') ? 'active' : ''?>">
                    <a class="nav-link" href="/users">Users</a>
                </li>
    <!--            <li class="nav-item --><?//=($action == 'admin') ? 'active' : ''?><!--">-->
    <!--                <a class="nav-link" href="/admin">Admin</a>-->
    <!--            </li>-->
            </ul>
        </div>

        <a class="btn btn-danger" href="/logout">Sign Out</a>

    </div>

</nav>

<div class="container pt-5">
    <?= Html::displayAlerts() ?>
    <?= $body ?>
</div>


<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="button_save">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="viewModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="confirmModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <button type="submit" class="btn btn-primary" id="button_save">Yes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>