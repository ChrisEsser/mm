<?php
?>

<h1 class="page_header">Categories</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/money/categories/create" role="button" class="btn btn-round btn-primary"><i class="bi bi-plus"></i>&nbsp;&nbsp;Add Category</a>
</div>

<table class="e2-table" id="categoryTable">
    <thead>
    <tr>
        <th>Primary</th>
        <th>Detail</th>
<!--        <th>Text</th>-->
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#categoryTable', {
            url: '/app-data/categories',
            sort: {first_name: 'ASC'},
            pageLength: 20,
            columns: [
                {col: 'primary_desc'},
                {col: 'detail_desc'},
                // {col: 'text_desc'},
                {col: '', sort: false, search: false,
                    cellStyle: 'text-align:right;',
                    template: function(data) {
                        let html = '<a href="/money/categories/edit/' + data.category_id + '" class="btn btn-outline-primary btn-sm me-md-1"><i class="fa fa-pencil"></i></a>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete <strong>' + data.title + '</strong>?" data-url="/categories/delete/' + data.category_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

    });

</script>
