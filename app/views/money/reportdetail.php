<?php

$title = $this->getVar('title');
$merchant = $this->getVar('merchant');

$end = date('M t Y', time());
$start = date('M 01 Y', strtotime('-1 year', strtotime($end)));

?>

<h1 class="page_header"><?=($merchant) ? 'Merchant' : 'Transaction Title'?> Detail Report</h1>

<h5><small><?=($title) ? $title : $merchant?></small></h5>

<?php if ($title) { ?>
    <p>Note: this report includes all transactions with titles very similar to this title. It may include other transactions.</p>
<?php } ?>

<p><?=$start?> - <?=$end?></p>


<div style="width: 100%;">

    <div class="row sparkboxes mb-4 d-flex">

        <div class="col-md-4">
            <div class="box box1">
                <div id="overallCount"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box2">
                <div id="overallSum"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box3">
                <div id="overallAverage"></div>
            </div>
        </div>
    </div>

    <div class="row mb-4 d-flex">
        <div class="col-md-6">
            <div class="box">
                <div id="countSeries"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div id="sumSeries"></div>
            </div>
        </div>
    </div>

</div>


<script>

    Apex.grid = {
        padding: {
            right: 0,
            left: 0
        }
    }
    Apex.dataLabels = {
        enabled: false
    }

    $(document).ready(function () {

        loadData();

    });

    function loadData() {

        let url = '/reports/get-data-detail';
        url += '?<?=($merchant) ? 'merchant=' . urlencode($merchant) : 'title=' . urlencode($title)?>';

        alert(url);

        $.get(url)
            .done(function(result) {
                result = JSON.parse(result);
                loadCharts(result.data);
            }).fail(function(result) {
            console.log(result);
            alert('Error getting report data');
        });
    }

    function loadCharts(data) {
        if (data.length) {
            loadTotalCount(data[0]);
            loadTotalSum(data[0]);
            loadTotalAverage(data[0]);
            loadCountSeries(data);
            loadSumSeries(data);
        }
    }

    function loadCountSeries(data) {

        $('#countSeries').html('');

        let series = [];
        let labels = [];

        let tmpObj = {};
        tmpObj.name = '';
        tmpObj.data = [];

        for(i = 1; i < data.length; i++) {
            labels.push(data[i].month);
            tmpObj.data.push(data[i].count);
        }
        series.push(tmpObj);

        var options = {
            series: series,
            chart: {
                type: 'bar',
                width: '100%',
                height: '400px',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Count Per Month',
                align: 'left'
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            markers: {
                size: 1
            },
            xaxis: {
                categories: labels,
                title: {
                    text: 'Month'
                },
                labels: {
                    show: false
                }
            },
            yaxis: {
                title: {
                    text: 'Count'
                },
                labels: {
                    show: false  // Hide x-axis labels
                }
            },
            legend: {
                position: 'top',
                // offsetY: 80
            }
        };

        revenueChart = new ApexCharts(document.querySelector("#countSeries"), options).render();

    }

    function loadSumSeries(data) {

        $('#sumSeries').html('');

        let series = [];
        let labels = [];

        let tmpObj = {};
        tmpObj.name = '';
        tmpObj.data = [];

        for(i = 1; i < data.length; i++) {
            labels.push(data[i].month);
            tmpObj.data.push(data[i].sum);
        }
        series.push(tmpObj);

        var options = {
            series: series,
            chart: {
                type: 'bar',
                width: '100%',
                height: '400px',
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Sum Per Month',
                align: 'left'
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            markers: {
                size: 1
            },
            xaxis: {
                categories: labels,
                title: {
                    text: 'Month'
                },
                labels: {
                    show: false
                }
            },
            yaxis: {
                title: {
                    text: 'Sum'
                },
                labels: {
                    show: false  // Hide x-axis labels
                }
            },
            legend: {
                position: 'top',
                // offsetY: 80
            }
        };

        revenueChart = new ApexCharts(document.querySelector("#sumSeries"), options).render();

    }


    function loadTotalCount(data) {

        $('#overallCount').html('');

        let series = [];
        let labels = [];
        let total = parseFloat(data.count);

        // for (let label in data.history) {
        //     if (data.history.hasOwnProperty(label)) {
        //         series.push(parseFloat(data.history[label]));
        //         labels.push(label);
        //     }
        // }

        const options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 100,
                sparkline: {
                    enabled: true
                },
            },
            stroke: {
                curve: 'straight'
            },
            fill: {
                opacity: 1,
            },
            series: [{
                name: 'Balance',
                data: series
            }],
            labels: labels,
            yaxis: {
                min: 0
            },
            xaxis: {
                type: 'datetime',
            },
            colors: ['#DCE6EC'],
            title: {
                text: total,
                offsetX: 30,
                offsetY: 15,
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Total Count',
                offsetX: 30,
                offsetY: 45,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        balanceChart = new ApexCharts(document.querySelector("#overallCount"), options).render();

    }

    function loadTotalSum(data) {

        $('#overallSum').html('');

        let series = [];
        let labels = [];
        let total = parseFloat(data.sum);

        // for (let label in data.history) {
        //     if (data.history.hasOwnProperty(label)) {
        //         series.push(parseFloat(data.history[label]));
        //         labels.push(label);
        //     }
        // }

        const options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 100,
                sparkline: {
                    enabled: true
                },
            },
            stroke: {
                curve: 'straight'
            },
            fill: {
                opacity: 1,
            },
            series: [{
                name: 'Balance',
                data: series
            }],
            labels: labels,
            yaxis: {
                min: 0
            },
            xaxis: {
                type: 'datetime',
            },
            colors: ['#DCE6EC'],
            title: {
                text: total.toLocaleString('en-US', { style: 'currency', currency: 'USD' }),
                offsetX: 30,
                offsetY: 15,
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Overall Sum',
                offsetX: 30,
                offsetY: 45,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        balanceChart = new ApexCharts(document.querySelector("#overallSum"), options).render();

    }

    function loadTotalAverage(data) {

        $('#overallAverage').html('');

        let series = [];
        let labels = [];
        let total = parseFloat(data.average);

        // for (let label in data.history) {
        //     if (data.history.hasOwnProperty(label)) {
        //         series.push(parseFloat(data.history[label]));
        //         labels.push(label);
        //     }
        // }

        const options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 100,
                sparkline: {
                    enabled: true
                },
            },
            stroke: {
                curve: 'straight'
            },
            fill: {
                opacity: 1,
            },
            series: [{
                name: 'Average',
                data: series
            }],
            labels: labels,
            yaxis: {
                min: 0
            },
            xaxis: {
                type: 'datetime',
            },
            colors: ['#DCE6EC'],
            title: {
                text: total.toLocaleString('en-US', { style: 'currency', currency: 'USD' }),
                offsetX: 30,
                offsetY: 15,
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Overall Average',
                offsetX: 30,
                offsetY: 45,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        balanceChart = new ApexCharts(document.querySelector("#overallAverage"), options).render();

    }

</script>






