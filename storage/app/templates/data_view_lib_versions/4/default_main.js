// d3.js imported as d3.
// @url https://github.com/d3/d3/wiki
// @url https://d3-graph-gallery.com/index.html
//
// Results from database are available in the `jsonResults` array variable.
// RootDB helpers are availble with prefix `rdb.*`
//
// CTRL+D                       - to comment line(s)
// CTRL+ENTER  (CMD+ENTER @mac) - to save change and re-interpret javascript code, with current results.
// ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )


// Set the dimensions and margins of the graph
var divGraph = d3.select(".subgrid-area-data-view-view").node();

const margin = {top: 30, right: 30, bottom: 70, left: 60};
const width = refDiv.current.parentNode.clientWidth;
const height = refDiv.current.parentNode.clientHeight;

// Append the svg object to the body of the page
d3.selectAll('#' + refDiv.current.id + ' svg').remove();
const svg = d3.select(refDiv.current);
