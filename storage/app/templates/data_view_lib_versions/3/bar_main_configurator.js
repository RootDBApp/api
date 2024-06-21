const chartJsConfigurator = {
    columnSetup: {
        labels: {
            columnName: "x_label"
        },
        datasetNames: {
            columnName: "dataset_name"
        },
        datasetValues: {
            columnName: "dataset_value"
        }
    },
    chartJsSetup: {
        config: {
            data: {
                labels: [],
                datasets: []
            },
            options: {
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
            }
        }
    }
};
