}

const resizeObserver = new ResizeObserver((entries) => {

    if(refDiv.current) {
        window.requestAnimationFrame(() => {
            drawCharJsGraph();
        });
    }
});


if(refDiv.current) {
    resizeObserver.observe(refDiv.current.parentNode);
}
