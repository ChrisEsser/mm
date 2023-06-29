<?php

/** @var Report[] $reports */
$reports = $this->getVar('reports');

?>

<h1 class="page_header">Reports</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <a href="/reports/manage" role="button" class="btn btn-round btn-primary"><i class="bi bi-cogs"></i>&nbsp;&nbsp;Manage My Reports</a>
</div>


<div class="row d-flex">
    <?php foreach ($reports as $report) { ?>

        <div class="mb-2 col-sm-<?=$report->size?>">
            <div class="box">
                <div id="report_<?=$report->report_id?>"></div>
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

</script>
