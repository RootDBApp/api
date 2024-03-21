const data = {labels, datasets};

chart[DATA_VIEW_JS_ID] = cjs.Chart.getChart(refCanvas.current.id);

if (chart[DATA_VIEW_JS_ID]) {

    chart[DATA_VIEW_JS_ID].options = options;
    chart[DATA_VIEW_JS_ID].data = data;
    chart[DATA_VIEW_JS_ID].update();
} else {

    chart[DATA_VIEW_JS_ID] = new cjs.Chart(refCanvas.current, {type: 'bar', data, options});
}
