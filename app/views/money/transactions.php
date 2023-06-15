<?php

//$balances = $this->getVar('balances');

?>

<h1 class="page_header">Transactions</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/money/sync" role="button" class="btn btn-round btn-primary"><i class="bi bi-cloud-download"></i>&nbsp;&nbsp;Refresh Transactions</a>
</div>

<!--Available Balance:-->
<?php //foreach ($balances->accounts as $account) { ?>
<!--    <br />--><?php //=$account->name?><!-- - $--><?php //=number_format($account->balances->available, 2)?>
<?php //} ?>

<table class="e2-table" id="transactionTable">
    <thead>
    <tr>
        <th>Date</th>
        <th>Title</th>
        <th>Merchant</th>
        <th>Amount</th>
        <th>Category</th>
        <th></th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>

    $(document).ready(function() {

        var table = new tableData('#transactionTable', {
            url: '/app-data/transactions',
            sort: {first_name: 'ASC'},
            pageLength: 100,
            columns: [
                {col: 'date', format: 'date'},
                {col: 'title',
                    template: function(data) {
                        if (data.title) {
                            return '<a href="/money/reports/detail?title=' + encodeURI(data.title) + '">' + data.title + '</a>';
                        } else return '';
                    }
                },
                {col: 'merchant',
                    template: function(data) {
                        if (data.merchant) {
                            return '<a href="/money/reports/detail?merchant=' + encodeURI(data.merchant) + '">' + data.merchant + '</a>';
                        } else return '';
                    }
                },
                {col: 'amount', format: 'usd'},
                {col: 'category'},
                {col: '', sort: false, search: false,
                    cellStyle: 'text-align:right;',
                    template: function(data) {
                        let html = '<a href="/money/transactions/edit/' + data.transaction_id + '" class="btn btn-outline-primary btn-sm me-md-1"><i class="fa fa-pencil"></i></a>';
                        // html += '<button role="button" class="btn btn-outline-danger btn-sm me-md-1 confirm_trigger" data-message="Are you sure you want to delete <strong>' + data.title + '</strong>?" data-url="/money/tranactions/delete/' + data.transaction_id + '" type="button"><i class="fa fa-times"></i></button>';
                        return html;
                    }
                },
            ]
        });

    });

</script>



