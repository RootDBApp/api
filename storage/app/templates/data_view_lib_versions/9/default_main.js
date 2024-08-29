// @url https://echarts.apache.org/examples/en/index.html
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

const dataset_names = jsonResults.map(row => row.x_label);

const series = [
    {
        type: 'bar',
        data: jsonResults.map((row) => row.dataset_value),
    }
];

chart[DATA_VIEW_JS_ID].setOption({
    xAxis: {
        type: 'category',
        data: dataset_names
    },
    yAxis: {
        type: 'value'
    },
    series: series
});
