<?php


?>

<h1 class="page_header">Users</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/users/create" role="button" class="btn btn-round btn-primary"><i class="bi bi-plus"></i>&nbsp;&nbsp;Add User</a>
</div>

<table class="e2-table" id="userTable">
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Admin</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#userTable', {
            url: '/app-data/users',
            sort: {first_name: 'ASC'},
            columns: [
                {col: 'first_name'},
                {col: 'last_name'},
                {col: 'email'},
                {col: 'admin', search: false,
                    template: function (data) {
                        return (data.admin == 1) ? 'Yes' : 'No';
                    }
                },
                {col: '', sort: false, search: false,
                    cellStyle: 'text-align:right;',
                    template: function(data) {
                        let html = '<a href="/users/edit/' + data.user_id + '" class="btn btn-outline-primary btn-sm me-md-1"><i class="fa fa-pencil"></i></a>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete <strong>' + data.first_name + '</strong>?" data-url="/users/delete/' + data.user_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

    });

</script>
