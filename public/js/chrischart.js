class ChrisChart {

    constructor(selector, options) {

        this.selector = selector;
        this.options = options;
        this.data = options.data;

        this.margin = {top: 20, right: 20, bottom: 40, left: 40};

        this.width = 800 - this.margin.left - this.margin.right;
        this.height = 400 - this.margin.top - this.margin.bottom;

        // Create SVG container
        this.svg = d3.select(this.selector)
            .append("svg")
            .attr("width", this.width + this.margin.left + this.margin.right)
            .attr("height", this.height + this.margin.top + this.margin.bottom)
            .append("g")
            .attr("transform", `translate(${this.margin.left},${this.margin.top})`);

        // Call the drawAxes function
        this.drawAxes();
    }

    drawAxes() {

        const xScale = d3.scaleBand()
            .domain(this.data.map(d => d.name))
            .range([0, this.width])
            .padding(0.1);

        const yScale = d3.scaleLinear()
            .domain([0, d3.max(this.data, d => d.value)])
            .range([this.height, 0]);

        const xAxis = d3.axisBottom(xScale);
        const yAxis = d3.axisLeft(yScale);

        const xAxisGroup = this.svg.append("g")
            .attr("transform", `translate(0, ${this.height})`)
            .call(xAxis);

        const yAxisGroup = this.svg.append("g")
            .call(yAxis);

        // Hide axis labels if drawAxisLabels is set to false
        if (this.options.drawAxisMarks === false) {
            xAxisGroup.selectAll(".tick")
                .attr("visibility", "hidden");
            yAxisGroup.selectAll(".tick")
                .attr("visibility", "hidden");
        }

        // Draw x-axis label
        if (typeof this.options.xAxis.label == 'string') {
            this.svg
                .append("text")
                .attr("x", this.margin.left + this.width / 2)
                .attr("y", this.margin.top + this.height + this.margin.bottom - 20)
                .style("text-anchor", "middle")
                .text(this.options.xAxis.label);
        }

        // Draw y-axis label
        if (typeof this.options.yAxis.label == 'string') {
            this.svg
                .append("text")
                .attr("transform", "rotate(-90)")
                .attr("x", -(this.margin.top + this.height / 2))
                .attr("y", this.margin.left - 70)
                .style("text-anchor", "middle")
                .text(this.options.yAxis.label);
        }
    }
}
