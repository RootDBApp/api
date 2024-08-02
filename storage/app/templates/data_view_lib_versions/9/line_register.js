let chart[DATA_VIEW_JS_ID];

if(refDiv.current) {

    if(!chart[DATA_VIEW_JS_ID]) {

        var chartDom[DATA_VIEW_JS_ID] = document.getElementById(refDiv.current.id);
        chart[DATA_VIEW_JS_ID] = ec.init(chartDom[DATA_VIEW_JS_ID], null, { renderer: 'svg' });
    }
}
