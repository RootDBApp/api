// @url https://echarts.apache.org/examples/en/index.html#chart-type-line
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

const labels = jsonResults.map(row => row.x_label);

const series = [
    {
        type: 'line',
        data: jsonResults.map((row) => row.dataset_value),
    }
];

chart[DATA_VIEW_JS_ID].setOption({
    xAxis: {
        type: 'category',
        data: labels
    },
    yAxis: {
        type: 'value'
    },
    grid: {
        left: '5%',
        right: '5%',
        top: '5%',
        bottom: '5%'
    },
    series: series
});
