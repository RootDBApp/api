const resizeObserver = new ResizeObserver((entries) => {

    if(refDiv.current) {
        window.requestAnimationFrame(() => {
            chart[DATA_VIEW_JS_ID].resize();
        });
    }
});


if(refDiv.current && chart[DATA_VIEW_JS_ID]) {

    // chart[DATA_VIEW_JS_ID].setOption(option);
    resizeObserver.observe(refDiv.current.parentNode);
}
