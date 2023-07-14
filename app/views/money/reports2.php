<?php

/** @var Report[] $reports */
$reports = $this->getVar('reports');

?>

<h1 class="page_header">Reports</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/money/reports" role="button" class="btn btn-round btn-primary"><i class="bi bi-arrow-left"></i>&nbsp;&nbsp;Back to Default Reports</a>
    <a href="/reports/manage" role="button" class="btn btn-round btn-primary"><i class="bi bi-cogs"></i>&nbsp;&nbsp;Manage My Reports</a>
</div>


<div class="row d-flex sortable">
    <?php foreach ($reports as $report) { ?>
        <div class=" mb-2 col-sm-<?=$report->size?> sortable-item" data-report="<?=$report->report_id?>">
            <div class="chart_box_outer">
                <div class="chart_box_inner">
                    <div class="chart_box_controls d-flex align-items-center justify-content-md-end">
                        <a href="/reports/edit/<?=$report->report_id?>" class="btn btn-sm" title="edit report"><i class="bi bi-pencil"></i></a>
                        <span class="btn btn-sm sortable_handle" title="move report"><i class="bi bi-arrows-move"></i></span>
                        <button type="button" class="btn btn-sm confirm_trigger" data-url="/reports/delete/<?=$report->report_id?>" data-title="Confirm Delete" data-message="Are you sure you want to delete this report?" title="delete report"><i class="bi bi-x"></i></button>
                    </div>
                    <div id="report_<?=$report->report_id?>"></div>
                </div>
            </div>
        </div>

    <?php } ?>
</div>

<script>

    Apex.dataLabels = {
        enabled: false
    }

    $(document).ready(function () {
        loadCharts();
    });

    function loadCharts() {

        var chart;
        var series = [];
        var labels = [];
        var total = 0;
        var options = {};
        var tmpObj = {};
        var labelsSet = false;

        <?php foreach ($reports as $report) { ?>

            $('#report_<?=$report->report_id?>').html('');

            $.get('/reports/get-data-custom/<?=$report->report_id?>').done(function (results) {

                series = [];
                labels = [];
                total = 0;
                options = {};
                labelsSet = false;

                try {

                    results = JSON.parse(results);
                    if (typeof results.data == 'undefined') throw "malformed";

                    for (i = 0; i < results.data.length; i++) {

                        <?php if ($report->getDetails()->graph_type !== 'pie') { ?>

                            tmpObj = {};
                            tmpObj.name = results.data[i].name;
                            tmpObj.data = [];

                            for (label in results.data[i].data) {
                                if (results.data[i].data.hasOwnProperty(label)) {
                                    tmpObj.data.push(results.data[i].data[label]);
                                    if (!labelsSet) {
                                        labels.push(label);
                                    }
                                }
                            }

                            labelsSet = true;
                            series.push(tmpObj);

                        <?php } else { ?>

                            series.push(results.data[i].data);
                            labels.push(results.data[i].name);

                        <?php } ?>

                    }

                    options = {
                        series: series,
                        labels: labels,
                        chart: {
                            type: '<?=$report->getDetails()->graph_type?>',
                            width: '100%',
                            height: '350px',
                            toolbar: {
                                show: true
                            }
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        stroke: {
                            curve: 'smooth'
                        },
                        title: {
                            text: "<?=$report->title?>",
                            align: 'left'
                        },
                        grid: {
                            enabled: false,
                            borderColor: '#e7e7e7',
                            row: {
                                colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                opacity: 0.5
                            },
                        },
                        markers: {
                            size: 1
                        },
                        legend: {
                            position: 'top',
                        },
                        yaxis: {
                            labels: {
                                show: false
                            }
                        },
                        xaxis: {
                            labels: {
                                show: false
                            }
                        },
                    };

                    chart = new ApexCharts(document.querySelector('#report_<?=$report->report_id?>'), options).render();

                } catch (e) {
                    console.log(results);
                    alert('Invalid response data:');
                }

            });

        <?php } ?>

    }

    $(document).ready(function() {

        $('.sortable').sortable({
            handle: '.sortable_handle',
            stop: function(event, ui) {
                let reportList = [];
                $('.sortable-item').each(function() {
                    reportList.push($(this).data('report'));
                });
                $.post('/reports/sort', {reportList: reportList}).done(function(response) {

                });
            }
        });
        $('.sortable').disableSelection();

    });


</script>
