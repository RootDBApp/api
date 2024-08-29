// @url https://echarts.apache.org/examples/en/index.html#chart-type-scatter
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

const series = [
    {
        type: 'scatter',
        data: jsonResults.map((row) => {
            return [row.x_value, row.y_value];
        }),
    }
];

chart[DATA_VIEW_JS_ID].setOption({
    xAxis: {},
    yAxis: {},
    grid: {
        left: '5%',
        right: '5%',
        top: '5%',
        bottom: '5%'
    },
    series: series
});
