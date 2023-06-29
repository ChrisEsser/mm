<?php


?>

<h1 class="page_header">My Reports</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/reports/create" role="button" class="btn btn-round btn-primary"><i class="bi bi-plus"></i>&nbsp;&nbsp;Create Report</a>
</div>

<table class="e2-table" id="reportsTable">
    <thead>
    <tr>
        <th>Title</th>
<!--        <th>Type</th>-->
        <th>Size</th>
        <th>Sort Order</th>
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#reportsTable', {
            url: '/app-data/reports',
            sort: {sort_order: 'ASC'},
            pageLength: 10,
            columns: [
                {col: 'title'},
                // {col: 'type'},
                {col: 'size', search: false,
                    template: function (data) {
                        if (data.size == 3) {
                            return 'Quarter Screen';
                        } else if (data.size == 4) {
                            return 'Third Screen';
                        } else if (data.size == 6) {
                            return 'Half Screen';
                        } else if (data.size == 8) {
                            return 'Two thirds Screen';
                        } else if (data.size == 9) {
                            return 'Three Quarter Screen';
                        } else if (data.size == 12) {
                            return 'Full Screen';
                        }
                    }
                },
                {col: 'sort_order', search: false,
                    template: function(data) {
                        return data.sort_order + 1;
                    }
                },
                {col: '', sort: false, search: false,
                    cellStyle: 'text-align:right;',
                    template: function(data) {
                        let html = '<a href="/reports/edit/' + data.report_id + '" class="btn btn-outline-primary btn-sm me-md-1"><i class="fa fa-pencil"></i></a>';
                        html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete <strong>' + data.title + '</strong>?" data-url="/reports/delete/' + data.report_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

    });

</script>


