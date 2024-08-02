// @url https://www.chartjs.org/docs/latest/samples/line/line.html
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

let dataset_names = [];

jsonResults.forEach(row => {

    if (!dataset_names.find((dataset_name) => dataset_name === row.dataset_name)) {

        dataset_names.push(row.dataset_name);
    }
});


let data_series = [];

jsonResults.forEach(row => {

    const data_serie_found = data_series.find((data_serie) => data_serie.name == row.dataset_name);
    if (!data_serie_found) {

        data_series.push({
            name: row.dataset_name,
            type: row.dataset_type,
            stack: row.dataset_stack,
            data: [row.dataset_value]
        });
    } else {

        data_series.map((data_serie) => {

            if (data_serie.name == row.dataset_name) {

                data_serie.data.push(row.dataset_value);
            }

            return data_serie;
        });
    }
});

const option = {
    tooltip: {
        trigger: 'axis'
    },
    legend: {
        data: dataset_names
    },
    grid: {
        left: '5%',
        right: '5%',
        bottom: '5%',
        top: '5%',
        containLabel: true
    },
    toolbox: {
        feature: {
            saveAsImage: {}
        }
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    },
    yAxis: {
        type: 'value'
    },
    series: data_series
};
