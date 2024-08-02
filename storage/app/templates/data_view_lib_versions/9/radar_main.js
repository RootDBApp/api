// @url https://echarts.apache.org/examples/en/index.html#chart-type-radar
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

        data_series.push({name: row.dataset_name, value: [row.dataset_value]});
    } else {

        data_series.map((data_serie) => {

            if (data_serie.name == row.dataset_name) {

                data_serie.value.push(row.dataset_value);
            }

            return data_serie;
        });
    }
});


const series = [
    {
        name: 'Budget vs spending',
        type: 'radar',
        data: data_series
    }
];

const option = {
    title: {
        text: 'Basic Radar Chart'
    },
    legend: {
        data: dataset_names
    },
    radar: {
        // shape: 'circle',
        indicator: [
            {name: 'Sales', max: 6500},
            {name: 'Administration', max: 16000},
            {name: 'Information Technology', max: 30000},
            {name: 'Customer Support', max: 38000},
            {name: 'Development', max: 52000},
            {name: 'Marketing', max: 25000}
        ]
    },
    series: series
};
