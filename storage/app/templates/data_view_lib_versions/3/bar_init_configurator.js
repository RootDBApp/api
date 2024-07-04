chart[DATA_VIEW_JS_ID] = cjs.Chart.getChart(refCanvas.current.id);

if (chart[DATA_VIEW_JS_ID]) {

    chart[DATA_VIEW_JS_ID].options = chartJsConfigurator.chartJsSetup.options;
    chart[DATA_VIEW_JS_ID].data = chartJsConfigurator.chartJsSetup.data;
    chart[DATA_VIEW_JS_ID].update();
} else {

    rdb.handleChartJsConfiguratorLabels(chartJsConfigurator);
    rdb.handleChartJsConfiguratorDatasets(chartJsConfigurator);

    chart[DATA_VIEW_JS_ID] = new cjs.Chart(
        refCanvas.current,
        {
            type: 'bar',
            chartJsConfigurator.chartJsSetup.data,
            chartJsConfigurator.chartJsSetup.options
        }
    );
}
