// @url https://www.chartjs.org/docs/latest/samples/bar/vertical.html
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// Chart ID : chart[DATA_VIEW_JS_ID]
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )

const labels = jsonResults.map(row => row.label);
//const labelValues = jsonResults.map(row => row.x_label_value); // If the label is not a value

const datasets = [
    {
        label: 'Dataset 1',
        data: jsonResults.map((row) => {

            return {
                x: row.dataset_value_x,
                y: row.dataset_value_y,
                r: row.dataset_value_r,
            }
        }),
        backgroundColor: rdb.backgroundColors,
    }
];

const options = {
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: rdb.getTextColor()
            }
        }
    },
    scales: {
        x: {
            ticks: {
                color: rdb.getTextColorSecondary()
            },
            grid: {
                color: rdb.getSurfaceBorder()
            }
        },
        y: {
            ticks: {
                color: rdb.getTextColorSecondary()
            },
            grid: {
                color: rdb.getSurfaceBorder()
            }
        }
    },
    /*  Use this to create a link to another report, when clicking on a part of the chart.
    onClick: (e) => {

       const points = chart[DATA_VIEW_JS_ID].getElementsAtEventForMode(e, 'nearest', {intersect: true}, true);

       if (points.length) {

           const firstPoint = points[0];
           var label = chart[DATA_VIEW_JS_ID].data.labels[firstPoint.index];
           //var labelValue = labelValues[firstPoint.index]; // If the label is not a value

           navigate(
               rdb.getReportPathWithParams(<linked_report_id>,
                   [
                       {key: '<linked_report_input_parameter_variable_name_1>', value: getValueParam('<current_report_input_parameter_variable_name_a>')},
                       {key: '<linked_report_input_parameter_variable_name_2>', value: getValueParam('<current_report_input_parameter_variable_name_b>')},
                       {key: '<linked_report_input_parameter_variable_name_2>', value: label} // Value below mouse cursor.
                       //{key: '<linked_report_input_parameter_variable_name_2>', value: labelValue} // Value below mouse cursor.
                   ]
               )
           );
       }
    },
    onHover: (e) => rdb.cjsOnHoverCursor(e, chart[DATA_VIEW_JS_ID])
    */
};