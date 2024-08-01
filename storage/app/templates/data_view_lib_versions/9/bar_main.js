// @url https://www.chartjs.org/docs/latest/samples/bar/vertical.html
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

const labels = jsonResults.map(row => row.x_label);
//const labelValues = jsonResults.map(row => row.x_label_value); // If the label is not a value

const series = [
    {
        type: 'bar',
        data: jsonResults.map((row) => row.dataset_value),
        backgroundColor: rdb.backgroundColors,
    }
];

const option = {
    xAxis: {
        type: 'category',
        data: labels
    },
    yAxis: {
        type: 'value'
    },
    series: series
};
