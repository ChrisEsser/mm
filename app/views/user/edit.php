<?php

/** @var \User $user */
$user = $this->getVar('user');

?>

<h1 class="page_header"><?=($user->user_id) ? 'Edit' : 'Create'?> User</h1>

<form method="POST" action="/users/save">

    <input type="hidden" name="user" id="user" value="<?=$user->user_id?>" />

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" aria-describedby="first_nameHelp" value="<?=$user->first_name?>" />
        </div>

        <div class="mb-3 col-md-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" aria-describedby="last_nameHelp" value="<?=$user->last_name?>" />
        </div>

    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp" value="<?=$user->email?>" />
    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-lock"></i>
                    </span>
                </span>
                <input type="password" class="form-control" id="password" name="password" aria-describedby="passwordHelp" value="" />
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="password_confirm" class="form-label">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-lock"></i>
                    </span>
                </span>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" aria-describedby="password_confirmHelp" value="" />
            </div>
        </div>

    </div>

    <div class="mb-3">
        <input type="checkbox" name="admin" id="admin" value="1" <?=($user->admin) ? ' checked' : ''?> />&nbsp;
        <label for="admin">Admin</label>
    </div>

    <div>
        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i>&nbsp;&nbsp;Save</button>
    </div>

</form>

<script>
    $(document).ready(function() {

    });
</script>