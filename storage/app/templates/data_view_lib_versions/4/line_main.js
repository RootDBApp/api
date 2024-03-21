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
const svg = d3.select(refDiv.current)
    .append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform",
        "translate(" + margin.left + "," + margin.top + ")");

const x_axis_range = width / jsonResults.length;

// X axis
const x = d3.scaleOrdinal()
    .domain(jsonResults.map(row => row.x_label))
    .range(jsonResults.map((data, idx) => {
        return x_axis_range * idx;
    }));

svg.append("g")
    .attr("transform", "translate(0, " + height + ")")
    .call(d3.axisBottom(x))
    .selectAll("text")
    .attr("stroke", rdb.getTextColor())
    .attr("transform", "translate(-10,0)rotate(-45)")
    .style("text-anchor", "end");

// Y axis
const y = d3.scaleLinear()
    .domain([0, d3.max(jsonResults, function (d) {
        return +Number(d.dataset_value);
    })])
    .range([height, 0]);

svg.append("g")
    .call(d3.axisLeft(y));

// Line
svg.append("path")
    .datum(jsonResults)
    .attr("fill", "none")
    .attr("stroke", "steelblue")
    .attr("stroke-width", 1.5)
    .attr("d", d3.line()
        .x(function (d) {
            return x(d.x_label)
        })
        .y(function (d) {
            return y(d.dataset_value)
        })
    );
