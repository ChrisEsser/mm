<?php

?>

<div class="col-md-6" id="chrischart"></div>

<script>

    $(document).ready(function() {

        let options = {
            chartType: 'bar',
            data: [
                { name: 'A', value: 20 },
                { name: 'B', value: 50 },
                { name: 'C', value: 30 },
                { name: 'D', value: 40 },
            ],
            // drawAxisMarks: false,
            width: '100%',
            height: '300px',
            xAxis: {
                label: 'X Axis'
            },
            yAxis: {
                label: 'Y Axis'
            }
        };

        let chart = new ChrisChart('#chrischart', options);

    });


</script>
