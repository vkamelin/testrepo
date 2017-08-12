$(function () {
    
    var dateStart = moment().subtract(13, 'days').format('YYYY-MM-DD');
    var dateFinal = moment().format('YYYY-MM-DD');
    
    // Sales summury

    // Get context with jQuery - using jQuery's .get() method.
    var areaChartCanvas = $('#salesSummury').get(0).getContext('2d');
    // This will get the first returned node in the jQuery collection.
    var areaChart = new Chart(areaChartCanvas);

    var areaChartData = {
        labels: salesLabels,
        datasets: [
            {
                label: '1 Cube Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: '#e9897e',
                pointColor: '#e9897e',
                pointStrokeColor: 'rgba(60, 141, 188, 1)',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#e9897e',
                data: product1Data
            },
            {
                label: '5 Cubes Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: '#dd4b39',
                pointColor: '#dd4b39',
                pointStrokeColor: 'rgba(60, 141, 188, 1)',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#dd4b39',
                data: product2Data
            },
            {
                label: '10 Cubes Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: '#d33724',
                pointColor: '#d33724',
                pointStrokeColor: 'rgba(60, 141, 188, 1)',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#d33724',
                data: product3Data
            },
            {
                label: '1 Spinner Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: '#59c593',
                pointColor: '#59c593',
                pointStrokeColor: '#59c593',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#59c593',
                data: product4Data
            },
            {
                label: '5 Spinners Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: "#00a65a",
                pointColor: '#00a65a',
                pointStrokeColor: '#00a65a',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#00a65a',
                data: product5Data
            },
            {
                label: '10 Spinners Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: "#008c4c",
                pointColor: '#008c4c',
                pointStrokeColor: '#008c4c',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#008c4c',
                data: product6Data
            },
            {
                label: '1+1 Mixed Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: "#59d6f4",
                pointColor: '#59d6f4',
                pointStrokeColor: '#59d6f4',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#59d6f4',
                data: product7Data
            },
            {
                label: '5+5 Mixed Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: "#00c0ef",
                pointColor: '#00c0ef',
                pointStrokeColor: '#00c0ef',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#00c0ef',
                data: product8Data
            },
            {
                label: '8+8 Mixed Pack',
                fillColor: 'rgba(60, 141, 188, 0.0)',
                strokeColor: "#00a7d0",
                pointColor: '#00a7d0',
                pointStrokeColor: '#00a7d0',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: '#00a7d0',
                data: product9Data
            }
        ]
    };

    var areaChartOptions = {
        //Boolean - If we should show the scale at all
        showScale: true,
        //Boolean - Whether grid lines are shown across the chart
        scaleShowGridLines: false,
        //String - Colour of the grid lines
        scaleGridLineColor: 'rgba(0, 0, 0, .05)',
        //Number - Width of the grid lines
        scaleGridLineWidth: 1,
        //Boolean - Whether to show horizontal lines (except X axis)
        scaleShowHorizontalLines: true,
        //Boolean - Whether to show vertical lines (except Y axis)
        scaleShowVerticalLines: true,
        //Boolean - Whether the line is curved between points
        bezierCurve: true,
        //Number - Tension of the bezier curve between points
        bezierCurveTension: 0.3,
        //Boolean - Whether to show a dot for each point
        pointDot: false,
        //Number - Radius of each point dot in pixels
        pointDotRadius: 4,
        //Number - Pixel width of point dot stroke
        pointDotStrokeWidth: 1,
        //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
        pointHitDetectionRadius: 20,
        //Boolean - Whether to show a stroke for datasets
        datasetStroke: true,
        //Number - Pixel width of dataset stroke
        datasetStrokeWidth: 2,
        //Boolean - Whether to fill the dataset with a color
        datasetFill: true,
        //String - A legend template
        legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
        //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        maintainAspectRatio: true,
        //Boolean - whether to make the chart responsive to window resizing
        responsive: true
    };

    //Create the line chart
    areaChart.Line(areaChartData, areaChartOptions);

    // Sales Amount
    
    var barChartCanvas = $('#salesAmount').get(0).getContext('2d');
    var barChart = new Chart(barChartCanvas);
    var barChartData = {
        labels: salesLabels,
        datasets: [
            {
                label: 'Sales',
                fillColor: '#00a65a',
                strokeColor: '#00a65a',
                pointColor: '#00a65a',
                pointStrokeColor: '#c1c7d1',
                pointHighlightFill: '#ffffff',
                pointHighlightStroke: 'rgba(220, 220, 220, 1)',
                data: totalsData
            }
        ]
    };

    var barChartOptions = {
        //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
        scaleBeginAtZero: true,
        //Boolean - Whether grid lines are shown across the chart
        scaleShowGridLines: true,
        //String - Colour of the grid lines
        scaleGridLineColor: 'rgba(0, 0, 0, .05)',
        //Number - Width of the grid lines
        scaleGridLineWidth: 1,
        //Boolean - Whether to show horizontal lines (except X axis)
        scaleShowHorizontalLines: true,
        //Boolean - Whether to show vertical lines (except Y axis)
        scaleShowVerticalLines: true,
        //Boolean - If there is a stroke on each bar
        barShowStroke: true,
        //Number - Pixel width of the bar stroke
        barStrokeWidth: 2,
        //Number - Spacing between each of the X value sets
        barValueSpacing: 5,
        //Number - Spacing between data sets within X values
        barDatasetSpacing: 1,
        //String - A legend template
        legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
        //Boolean - whether to make the chart responsive
        responsive: true,
        maintainAspectRatio: true
    };

    barChartOptions.datasetFill = false;
    barChart.Bar(barChartData, barChartOptions);
    
    
    /* Daterange */
    $('.daterange').daterangepicker({
        ranges: {
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 2 Weeks': [moment().subtract(13, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(13, 'days'),
        endDate: moment()
    }, function (start, end) {
        // window.alert("You chose: " + start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        dateStart = start.format('YYYY-MM-DD');
        dateFinal = end.format('YYYY-MM-DD');
        
        var href = $('#report-xls').attr('data-href');
        $('#report-xls').attr('href', href + '?dateStart=' + dateStart + '&dateFinal=' + dateFinal);
        
    });
    
});
