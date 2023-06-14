<?php

?>

<h1 class="page_header">Reports</h1>

<div class="mb-4">

    <div class="row">
        <div class="col-sm-3 mb-2">
            <label for="mode" class="form-label mb-0">Mode</label>
            <select class="form-control param_change" id="mode">
                <option value="month">Month</option>
                <option value="quarter">Quarter</option>
                <option value="year" selected>Year</option>
            </select>
        </div>
        <div class="col-sm-3 mb-2">
            <label for="year" class="form-label mb-0">Year</label>
            <select class="form-control param_change" id="year">
                <?php for ($i = date('Y'); $i >= date('Y') - 7; $i--) { ?>
                    <option value="<?=$i?>"><?=$i?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3 mb-2" id="period_container">
            <label for="period" class="form-label mb-0" id="period_label">Month</label>
            <select class="form-control param_change" id="period"></select>
        </div>
        <div class="col-sm-3">
            <label for="group" class="form-label mb-0">Group By</label>
            <select class="form-control param_change" id="group">
                <option value="primary">Primary</option>
                <option value="detail">Detail</option>
            </select>
        </div>
    </div>

</div>

<div class="clearfix"></div>


<style>

.box.banana_map {
    color: #fff;
    background: #eff4f7;
    padding: 0;
    box-shadow: none;
}
.box.banana_map .title {
    padding-top: 40px;
    padding-left: 25px;
    font-size: 16px;
}
.box.banana_map .subtitle {
    font-weight: 700;
    padding-top: 10px;
    padding-left: 25px;
    font-size: 22px;
}

.box {
    max-height: 444px;
}

.box .banana {
    min-height: 404px;
    background-image: url('/img/banana.png');
    background-size: cover;
}
.box .map {
    min-height: 404px;
    background-image: url('/img/map.png');
    background-size: cover;
}
.box .cog-icon {
    cursor: pointer;
    position: absolute;
    right: 55px;
    top: 25px;
    z-index: 10;
}

.sparkboxes .box {
    padding: 3px 0 0 0;
    position: relative;
}

#revenueChart, #expenseChart, #profitChart {
    position: relative;
    padding-top: 15px;
}
/* overrides */
.sparkboxes #apexcharts-subtitle-text { fill: #8799a2 !important; }

.box {
    box-shadow: 0px 1px 22px -12px #607D8B;
    background-color: #fff;
    padding: 25px 35px 25px 30px;
    height: 100%;
}
</style>






<!--<div style="width: 100%; background-color: #eff4f7;">-->
<div style="width: 100%; ">

    <div class="row sparkboxes mb-4 d-flex">

        <div class="col-md-4">
            <div class="box box1">
                <div id="balanceChart"></div>
<!--                div id="profitChart"></div>-->
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box2">
                <div id="revenueChart"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box3">
                <div id="expenseChart"></div>
            </div>
        </div>
    </div>

    <div class="row mb-4 d-flex">
        <div class="col-md-6">
            <div class="box">
                <div id="pieChart"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div id="areaChart"></div>
            </div>
        </div>
    </div>

<!--    <div class="row mt-4 mb-4">-->
<!--        <div class="col-md-6">-->
<!--            <div class="box">-->
<!--                <div id="area"></div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="col-md-6">-->
<!--            <div class="box">-->
<!--                <div id="line"></div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

</div>



<script>

    var pieChart = {};
    var balanceChart = {};
    var revenueChart = {};
    var expenseCahrt = {};
    var profitChart = {};
    var colorPalette = ['#00D8B6','#008FFB',  '#FEB019', '#FF4560', '#775DD0'];

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

        $('.param_change').change(function() {
            const selectId = $(this).attr('id');
            if (selectId === 'period') {
                loadData();
            } else {
                loadPeriodValues(loadData);
            }
        })

        loadPeriodValues(loadData);
    });

    function loadData() {

        const mode = $('#mode').val();
        const year = $('#year').val();
        const period = $('#period').val();
        const group = $('#group').val();

        let url = '/reports/get-data';
        url += '?mode=' + mode;
        url += '&year=' + year;
        url += '&period=' + period;
        url += '&group=' + group;

        $.get(url)
            .done(function(result) {
                result = JSON.parse(result);
                loadCharts(result.data, mode);
            }).fail(function(result) {
            console.log(result);
            alert('Error getting report data');
        });
    }

    function loadPeriodValues(callback) {

        $('#period').empty();

        const mode = $('#mode').val();
        if (mode == 'month') {
            $('#period_label').text('Month');
            var currentDate = new Date();
            var currentMonth = currentDate.getMonth();
            for (var i = 0; i < 12; i++) {
                $('#period').append('<option value="' + i + '" ' + ((currentMonth == i) ? 'selected' : '') + '>' + monthText(i) + '</option>');
            }
            $('#period_container').show();
        } else if (mode == 'quarter') {
            $('#period_label').text('Quarter');
            var currentDate = new Date();
            var currentMonth = currentDate.getMonth() + 1;
            var currentQuarter = Math.ceil(currentMonth / 3);
            for (var i = 1; i <= 4; i++) {
                $('#period').append('<option value="' + i + '" ' + ((currentQuarter == i) ? 'selected' : '') + '>' + i + '</option>');
            }
            $('#period_container').show();
        } else if (mode == 'year') {
            $('#period_container').hide();
        }

        if (typeof callback == 'function') {
            callback();
        }
    }

    function monthText(monthNumber) {
        return new Date(0, monthNumber).toLocaleString('default', { month: 'long' });
    }

    function loadCharts(data, mode) {
        loadRevenueChart(data.expenseRevenue);
        loadExpenseChart(data.expenseRevenue);
        loadProfitChart(data.expenseRevenue);
        loadCategorySpendingChart(data.categorySpending);
        loadAreaChart(data.categorySpendingGrouped, mode);
        loadBalanceChart(data.balance);
    }

    function loadBalanceChart(data) {

        $('#balanceChart').html('');

        let series = [];
        let labels = [];
        let total = parseFloat(data.amount);
        let lastSynced = data.date;

        var options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 160,
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
                name: 'Revenue',
                data: series
            }],
            labels: labels,
            colors: ['#DCE6EC'],
            title: {
                text: total.toLocaleString('en-US', { style: 'currency', currency: 'USD' }),
                offsetX: 30,
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Current Balance',
                offsetX: 30,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        balanceChart = new ApexCharts(document.querySelector("#balanceChart"), options).render();

    }

    function loadCategorySpendingChart(data) {

        $('#pieChart').html('');

        let series = [];
        let labels = [];
        for (i = 0; i < data.length; i++) {
            if (data[i].amount > 0) {
                series.push(parseFloat(data[i].amount));
                labels.push(data[i].description);
            }
        }

        var options = {
            chart: {
                type: 'donut',
                width: '100%',
            },
            dataLabels: {
                enabled: false,
            },
            plotOptions: {
                pie: {
                    customScale: 0.8,
                    donut: {
                        size: '75%',
                    },
                    offsetY: 20,
                },
                stroke: {
                    colors: undefined
                }
            },
            colors: colorPalette,
            title: {
                text: 'Percent of Spending',
                align: 'left'
            },
            series: series,
            labels: labels,
            legend: {
                position: 'top',
                // offsetY: 80
            }
        }

        pieChart = new ApexCharts(
            document.querySelector("#pieChart"),
            options
        )
        pieChart.render();
    }

    function loadRevenueChart(data) {

        $('#revenueChart').html('');

        let series = [];
        let labels = [];
        let total = 0.0;
        let tmpValue = 0;
        for (let label in data) {
            if (data.hasOwnProperty(label)) {
                tmpValue = parseFloat(data[label].income) * -1;
                series.push(tmpValue);
                total += tmpValue;
                labels.push(label);
            }
        }

        var options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 160,
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
                name: 'Revenue',
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
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Total Income',
                offsetX: 30,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options).render();
    }

    function loadExpenseChart(data) {

        $('#expenseChart').html('');

        let series = [];
        let labels = [];
        let total = 0.0;
        for (let label in data) {
            if (data.hasOwnProperty(label)) {
                series.push(parseFloat(data[label].expense));
                total += parseFloat(data[label].expense);
                labels.push(label);
            }
        }

        var options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 160,
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
                name: 'Expense',
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
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Total Expenses',
                offsetX: 30,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        expenseCahrt = new ApexCharts(document.querySelector("#expenseChart"), options).render();

    }

    function loadProfitChart(data) {

        $('#profitChart').html('');

        let series = [];
        let labels = [];
        let total = 0.0;
        for (let label in data) {
            if (data.hasOwnProperty(label)) {
                series.push(parseFloat(data[label].profit));
                total += parseFloat(data[label].profit);
                labels.push(label);
            }
        }

        var options = {
            chart: {
                id: 'sparkline1',
                group: 'sparklines',
                type: 'area',
                height: 160,
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
                name: 'Profit',
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
                style: {
                    fontSize: '24px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            },
            subtitle: {
                text: 'Bottom Line',
                offsetX: 30,
                style: {
                    fontSize: '14px',
                    cssClass: 'apexcharts-yaxis-title'
                }
            }
        }

        // profitChart = new ApexCharts(document.querySelector("#profitChart"), options).render();

    }

    function loadAreaChart(data, mode) {

        $('#areaChart').html('');

        let series = [];
        let labels = [];
        let labelsSet = false;
        for (let categoryName in data) {
            let tmpObj = {};
            tmpObj.name = categoryName;
            tmpObj.data = [];
            for (let label in data[categoryName]) {
                if (!labelsSet) {
                    labels.push(label);
                }
                tmpObj.data.push(data[categoryName][label]);
            }
            series.push(tmpObj);
            labelsSet = true;
        }

        var options = {
            series: series,
            chart: {
                type: 'line',
                width: '100%',
                toolbar: {
                    show: false
                }
            },
            colors: colorPalette,
            dataLabels: {
                enabled: false,
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: 'Category Spending',
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
                categories: [],
                title: {
                    text: (mode == 'month') ? 'Day' : 'Month'
                }
            },
            yaxis: {
                title: {
                    text: 'Spent'
                }
            },
            legend: {
                position: 'top',
                // offsetY: 80
            }
        };

        var chartArea = new ApexCharts(document.querySelector('#areaChart'), options);
        chartArea.render();

    }

</script>
