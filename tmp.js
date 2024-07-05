cjs.Chart.register(
    cjs.BarElement,
    cjs.BarController,
    cjs.CategoryScale,
    cjs.Legend,
    cjs.LinearScale,
    cjs.Title,
    cjs.Tooltip
);

let chart58;


const chartJsConfigurator = rdb.initChartJsConfigurator();

chart58 = cjs.Chart.getChart(refCanvas.current.id);

if (chart58) {

    chart58.options = chartJsConfigurator.chartJsSetup.options;
    chart58.data = chartJsConfigurator.chartJsSetup.data;
    chart58.update();
} else {

    rdb.handleChartJsConfiguratorLabels(chartJsConfigurator);
    rdb.handleChartJsConfiguratorDatasets(chartJsConfigurator);

    chart58 = new cjs.Chart(
        refCanvas.current,
        {
            type: 'bar',
            chartJsConfigurator.chartJsSetup.data,
            chartJsConfigurator.chartJsSetup.options
        }
    );
}
