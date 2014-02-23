
var TWCAdvancedChart = {

	chartType: null,
	cachedData: [],
	shownData: null,

	container: null,
	svg: null,
	boxSize: {},
	tooltip: null,

	graphSeries: [],
	numPoints: 0,
	xAxis: null,
	yAxis: [],

	width: null,
	height: null,
	margin: {},

	rangeSelection: [null, null],
	selectionLayer: null,

	chartOptions: {},
	dotStyle: null,

	init: function (options) {

		this.chartType = options.chartType;
		this.chartOptions = options.chartOptions;

		this.baseFrequency = 5; // 5 min
		this.chartOptions.frequency = 120; // 2 hr

		this.parseData(options.data);
		this.shownDataDeepCopy();

		this.container = d3.select(options.parent[0]).select('svg'); // TODO: pass an svg element
		this.boxSize = options.boxSize || {width: '750px', height: '300px'};
		this.tooltip = options.tooltip || null;
		this.setInitialSize();

		this.width = this.boxSize.width;
		this.height = this.boxSize.height;
		this.margin = {top: 8, right: 16, bottom: 18, left: 8};

		this.rangeSelection = [null, null];
		this.selectionLayer = null;

		this.dotStyle = this.defaultDotStyle();

		this.chartOptions.events = {};

		this.build();
	},

	update: function (options) {
		this.chartType = options.chartType;
		this.parseData(options.data);
		this.shownDataDeepCopy();
		this.emptyAndBuild();
	},

	parseData: function (d) {
		var seriesLen = d.metrics.length;
		var dataLen = d.values.length;

		this.cachedData = [];
		for ( var i = 0 ; i < seriesLen ; i++ ) {
			this.cachedData.push({
				key: d.metrics[i],
				color: this.chartOptions.colors[i],
				values: []
			});
		}

		for ( var i = 0 ; i < dataLen ; i++ ) {
			var item = d.values[i];
			for ( var j = 0 ; j < seriesLen ; j++ ) {
				this.cachedData[j].values.push({x: item[0], y: item[1][j]});
			}
		}
	},

	setInitialSize: function () {
		// Fix for FF
		this.container.attr("width", this.boxSize.width + "px");
		this.container.attr("height", this.boxSize.height + "px");
	},

	graphMouseMove: function (e) {
		var mouse = d3.mouse(e);
		var xPos = mouse[0] - this.margin.left;
		var xRange = this.width;

		var xBlock = (this.chartType == 'bar')
			? xRange / (this.numPoints)
			: xRange / (this.numPoints - 1);

		if ( xPos > 0 && xPos <= xRange) {
			this.hideTooltip();

			var blockNum = (this.chartType == 'bar')
				? Math.round(xPos / xBlock + 0.5) - 1
				: Math.round(xPos / xBlock);

			this.container.selectAll('.dot').attr("stroke-width", this.dotStyle.stroke).attr('r', this.dotStyle.radius);
			this.container.selectAll('.dot-' + blockNum).attr("stroke-width", this.dotStyle.hlStroke).attr('r', this.dotStyle.hlRadius);

			this.container.selectAll('.yAxis').attr("opacity", 0.1);

			if (this.inRangeSelection()) {
				/* Show election area */
				var x1 = this.rangeSelection[0];
				var x2 = blockNum;
				if (x1 == x2) {
					this.selectionLayer.attr("width", 0); // hides selection layer
				}
				else {
					if (x2 < x1) {
						x2 = x1;
						x1 = blockNum;
					}
					this.selectionLayer
						.attr("width", xBlock * (x2 - x1))
						.attr("transform", "translate(" + (this.margin.left + (xBlock * x1)) + "," + this.margin.top + ")")
				}
			}
			else {
				this.updateTooltip(mouse, blockNum);
			}
		}
	},

	graphMouseOut: function () {
		this.container.selectAll('.dot').attr("stroke-width", this.dotStyle.stroke).attr('r', this.dotStyle.radius);
		this.container.selectAll('.yAxis').attr("opacity", 1);
		if (this.selectionLayer) {
			this.selectionLayer.attr("width", 0); // hides selection layer
		}
		this.hideTooltip();
	},

	graphMouseClick: function (e) {
		var mouse = d3.mouse(e);
		var xPos = mouse[0] - this.margin.left;
		var xRange = this.width;
		var xBlock = xRange / (this.numPoints - 1);
		var idx = Math.round(xPos / xBlock);

		if (this.chartOptions.events.click) {
			if (!this.shownData[0].values[idx]) {
				return;
			}
			this.chartOptions.events.click.cb(this.shownData[0].values[idx]);
		}
		else {
			this.defaultMouseClick(idx);
		}
	},

	defaultMouseClick: function (idx) {
		if (!this.inRangeSelection()) {
			this.rangeSelection[0] = idx;
		}
		else {
			this.rangeSelection[1] = idx;
			this.updateShownData();
		}
	},

	graphNiceLimit: function (num, orient, diff) {
		// num is always positive
		var ref = (diff) ? Math.abs(diff) : num;

		var base = null;
		if (ref < 10) {
			base = 2;
		}
		else if (ref < 100) {
			base = 5;
		}
		else {
			var order = parseInt(Math.log(ref) / Math.LN10) - 1;
			base = Math.pow(10, order);
		}

		var nice = null;
		if (orient == 'top') {
			nice = (num + base) / base;
			nice = Math.floor(nice);
		}
		else { // bottom
			nice = (num - base) / base;
			nice = (nice <= 0) // No negative values in y axis
				? 0
				: Math.ceil(nice);
		}

		return nice * base;
	},

	graphHorizontalLines: function () {
		var style = {'stroke': '#808080', 'strokeWidth': '1px', 'opacity': 0.5}
		this.svg.append("svg:line").attr("x1", 0).attr("y1", 0).attr("x2", this.width).attr("y2", 0)
			.attr("transform", "translate(" + this.margin.left + "," + this.margin.top + ")")
			.attr("stroke", style.stroke).attr("stroke-width", style.strokeWidth).attr("opacity", style.opacity);

		var middHeight = Math.round(this.height/2);
		this.svg.append("svg:line").attr("x1", 0).attr("y1", 0).attr("x2", this.width).attr("y2", 0)
			.attr("transform", "translate(" + this.margin.left + "," + (this.margin.top + middHeight) + ")")
			.attr("stroke", style.stroke).attr("stroke-width", style.strokeWidth).attr("opacity", style.opacity);

		this.svg.append("svg:line").attr("x1", 0).attr("y1", 0).attr("x2", this.width).attr("y2", 0)
			.attr("transform", "translate(" + this.margin.left + "," + (this.margin.top + this.height) + ")")
			.attr("stroke", style.stroke).attr("stroke-width", style.strokeWidth).attr("opacity", style.opacity);
	},

	graphBackground: function () {
		this.container.insert("rect", ":first-child")
			.attr("class", "twc-graph-bg")
			.attr("width", this.boxSize.width)
			.attr("height", this.boxSize.height)
			.attr("fill", "#FFFFFF");
	},

	graphRangeSelectionLayer: function () {
		this.selectionLayer = this.svg.append("rect")
			.attr("class", "twc-graph-selection")
			.attr("width", 0)
			.attr("height", this.height)
			.attr("opacity", 0.25)
			.attr("transform", "translate(" + this.margin.left + "," + this.margin.top + ")")
			.attr("fill", "#888888");
	},

	graphAxis: function () {
		var that = this;

		// Create X axis
		if (this.xAxis != null) { // TODO: change this!
			var xAxisSvg = this.svg.append("g")
				.attr('font-size', "10px")
				.attr("class", "xAxis")
				.attr("transform", "translate(" + this.margin.left + "," + (this.margin.top + this.height) + ")")
				.call(this.xAxis);
			// hide lines
			xAxisSvg.select('path').attr('opacity', 0);
			xAxisSvg.select('line').attr('opacity', 0);
		}

		// Create Y axis
		$.each(this.yAxis, function (idx, axis) {
			var marginLeft = ( idx == 0 )
				? that.margin.left
				: that.margin.left + that.width;
			var yAxisSvg = that.svg.append("g")
				.attr("class", "yAxis")
				.attr('font-size', "9px")
				.attr("transform", "translate(" + marginLeft + "," + that.margin.top + ")")
				.call(axis);
			// hide lines
			yAxisSvg.select('path').attr('opacity', 0);
			yAxisSvg.select('line').attr('opacity', 0);
		});

		var tickBoxPadding = 6;
		this.svg.selectAll(".yAxis").each(function(){
			var yAxis = d3.select(this);
			yAxis.selectAll(".tick").each(function(){
				var tick = d3.select(this);
				tick.select('text').each(function(){
					var box = this.getBBox();

					tick.insert("rect", ":first-child")
						.attr("class", "tick-bg")
						.attr("x", box.x - (tickBoxPadding/2))
						.attr("y", box.y)
						.attr("rx", 2)
						.attr("ry", 2)
						.attr("width", box.width + tickBoxPadding)
						.attr("height", box.height)
						.attr("fill", "#FFFFFF")
						.attr("opacity", 0.75);
				});
			});
		});
	},

	graphDrawData: function () {
		var that = this;

		if (this.area) {
			// First draw areas
			var areaGradient = this.svg.append("svg:defs")
				.append("svg:linearGradient")
				.attr("id", "areaGradient")
				.attr("x1", "0%")
				.attr("y1", "0%")
				.attr("x2", "0%")
				.attr("y2", "100%")
				.attr("spreadMethod", "pad");

			areaGradient.append("svg:stop")
				.attr("offset", "0%")
				.attr("stop-color", this.graphSeries[0].color)
				.attr("stop-opacity", .18);
			areaGradient.append("svg:stop")
				.attr("offset", "100%")
				.attr("stop-color", this.graphSeries[1].color)
				.attr("stop-opacity", .10);

			this.container.append("svg:path")
				.style("fill", "url(#areaGradient)")
				.attr("transform", "translate(" + this.margin.left + "," + this.margin.top + ")")
				.attr("d", this.area.gen(that.area.values));
		}

		var dotStyle = this.dotStyle;
		$.each(this.graphSeries, function (idx, serie) {
			that.svg.append("svg:path")
				.attr("fill", "none")
				.attr("stroke", serie.color)
				.attr("stroke-width", "2px")
				.attr("transform", "translate(" + that.margin.left + "," + that.margin.top + ")")
				.attr("d", serie.line(serie.values));

			that.svg.selectAll("scatter-dots")
				.data(serie.values)
				.enter().append("svg:circle")
				.attr("class", function (d,i) { return 'dot dot-' + i; } )
				.attr("fill", serie.color)
				.attr("stroke", '#FFFFFF')
				.attr("stroke-width", dotStyle.stroke)
				.attr("cx", function (d,i) { return serie.x(d.x); } )
				.attr("cy", function (d) { return serie.y(d.y); } )
				.attr("r", dotStyle.radius)
				.attr("transform", "translate(" + that.margin.left + "," + that.margin.top + ")");
		});

	},

	getTooltipMetrics: function (idx) {
		var that = this;
		var metrics = $('<div>').attr('class', 'metric');
		$.each(this.shownData, function (i, serie) {
			$('<span>').css('color', serie.color).html('&#9679;').appendTo(metrics);
			$('<span>').text(' ' + serie.key).appendTo(metrics);
			metrics.append(' ' + numberFormat(serie.values[idx].y, 2))
				.append('<br>');
		});

		if (this.shownData.length == 2) {
			var diff = this.shownData[0].values[idx].y - this.shownData[1].values[idx].y;
			metrics.append('<span>diff:</span> ' + numberFormat(diff, 2));
		}

		return metrics;
	},

	hideTooltip: function () {
		this.tooltip.css('display', 'none');
	},

	placeTooltip: function (mouse) {
		var xOffset = 22;
		var top = mouse[1] + 8; // offset: 8
		this.tooltip.css({'display': 'block', 'top': top + 'px'});
		var width = this.boxSize.width;
		if (mouse[0] > (width / 2)) {
			this.tooltip.css('left', '').css('right', ((width - mouse[0]) + xOffset) + 'px');
		}
		else {
			this.tooltip.css('left', (mouse[0] + xOffset) + 'px').css('right', '');
		}
	},

	updateTooltip: function (mouse, idx) {

		if (!this.shownData[0].values[idx]) {
			return;
		}

		var date = moment(this.shownData[0].values[idx].x);
		var dateStr = {'day': date.format('ddd'), 'date': date.format('MMM D'), 'year': date.format('YYYY'), 't': date.format('HH:mm')};
		date = $('<div>').attr('class', 'date')
			.html('<b>' + dateStr.t + '</b>')
			.append(', ' + dateStr.day + ', ' + dateStr.date + ' ')
			.append($('<span>').text(dateStr.year))
		var metrics = this.getTooltipMetrics(idx);

		this.tooltip.empty().append(date).append(metrics);
		this.placeTooltip(mouse);
	},

	getAxisFormat: function () {

		var values = this.shownData[0].values;
		var numPoints = values.length; // at least we have one data serie // TODO: change to this.numPoints

		var timeFormat = '%m/%d %H:%M';
		var xTickWidth = 70;

		var ticksNum = Math.floor(this.boxSize.width / xTickWidth);

		while (ticksNum > 2) {
			var slots = ticksNum - 1;
			if ((numPoints - ticksNum) % slots == 0) {
				break;
			}
			else {
				ticksNum--;
			}
		}

		var xAxisSlots = ticksNum - 1;
		var slotSize = (numPoints - ticksNum) / xAxisSlots;

		var xAxisValues = new Array();
		for ( var idx = 0 ; idx <= xAxisSlots ; idx++ ) {
			xAxisValues.push({
				'idx': (slotSize+1) * idx,
				'val':  new Date(values[ (slotSize+1) * idx ].x)
			});
		}

		return {'text': timeFormat, 'values': xAxisValues, 'totalTicks': numPoints};
	},

	getLineSeriesData: function (bounds) {
		var that = this;

		var xAxis = null;
		var yAxis = new Array();

		var graphSeries = new Array();

		$.each(this.shownData, function (idx, serie) {

			graphSeries[idx] = serie;

			if (bounds) {
				var seriesMin = bounds.seriesMin;
				var seriesMax = bounds.seriesMax;
			}
			else {
				var seriesMin = d3.min(serie.values, function(d) { return d.y; });
				var seriesMax = d3.max(serie.values, function(d) { return d.y; });
			}

			var seriesDiff = seriesMax - seriesMin;

			seriesMin = that.graphNiceLimit(seriesMin, 'bottom', seriesDiff);
			seriesMax = that.graphNiceLimit(seriesMax, 'top', seriesDiff);

			graphSeries[idx].x = d3.scale.linear().range([0, that.width]);
			graphSeries[idx].y = d3.scale.linear().range([that.height, 0]);

			var middle = (seriesMax + seriesMin) / 2;
			var yAxisOrient = (idx == 0) ? 'right' : 'left';
			yAxis[idx] = d3.svg.axis()
				.scale(graphSeries[idx].y)
				.orient(yAxisOrient)
				.tickValues([seriesMin, middle, seriesMax])
				.tickFormat(function(d){ return numberFormat(d, 2); });

			graphSeries[idx].line = d3.svg.line()
				.x(function(d, i) {
					return graphSeries[idx].x(d.x); })
				.y(function(d) { return graphSeries[idx].y(d.y); })
				.interpolate('monotone');

			graphSeries[idx].x.domain(d3.extent(serie.values, function(d) { return d.x; }));
			graphSeries[idx].y.domain([seriesMin, seriesMax]);
		});

		this.area = null;

		if (this.shownData.length == 2) {
			this.area = {};

			this.area.values = [];
			for ( var i = 0 ; i < this.numPoints ; i++ ) {
				this.area.values.push({
					x:  this.shownData[0].values[i].x,
					y0: this.shownData[0].values[i].y,
					y1: this.shownData[1].values[i].y });
			}

			this.area.gen = d3.svg.area()
				.x(function(d) { return graphSeries[0].x(d.x); })
				.y0(function(d) { return graphSeries[0].y(d.y0); })
				.y1(function(d) { return graphSeries[0].y(d.y1); })
				.interpolate('monotone');
		}

		var xAxisFormat = this.getAxisFormat();

		xAxis = d3.svg.axis()
			.scale(graphSeries[0].x)
			.orient("bottom")
			.ticks(xAxisFormat.totalTicks)
			.tickValues(xAxisFormat.values.map(function(d) { return d.val; }))
			.tickFormat(d3.time.format(xAxisFormat.text));

		this.graphSeries = graphSeries;
		this.xAxis = xAxis;
		this.yAxis = yAxis;
	},

	buildLineGraph: function () {
		var that = this;

		this.margin = {top: 15, right: 30, bottom: 20, left: 30};
		this.width = this.boxSize.width - this.margin.left - this.margin.right;
		this.height = this.boxSize.height - this.margin.top - this.margin.bottom;

		this.numPoints = this.shownData[0].values.length; // at least we have one data serie

		this.setInitialSize();
		this.setDotStyle();

		this.svg = this.container.append("svg:g")
			.attr("width", this.boxSize.width + "px")
			.attr("height", this.boxSize.height + "px");

		var bounds = null;
		if (typeof this.chartOptions.dual_axis != 'undefined' && !this.chartOptions.dual_axis) { // if dual_axis is set and set to false
			bounds = {
				seriesMin: d3.min(this.shownData, function(serie) { return d3.min(serie.values, function(d) { return d.y; }); }),
				seriesMax: d3.max(this.shownData, function(serie) { return d3.max(serie.values, function(d) { return d.y; }); })
			};
		}

		var seriesData = this.getLineSeriesData(bounds);

		this.graphHorizontalLines();
		this.graphAxis();
		this.graphDrawData();
		this.graphBackground();
		this.graphRangeSelectionLayer();

		this.container.on("mousemove", function() {
			that.graphMouseMove(this);
		})
			.on("mouseout", function() {
				that.graphMouseOut();
			})
			.on("click", function (){
				that.graphMouseClick(this);
			});
	},

	build: function () {
		switch (this.chartType) {
			case 'line':
			default:
				this.buildLineGraph();
				break;
		}
	},

	emptyGraph: function () {
		// Remove all children of this.container
		// this.container is a d3 element, not a jQuery element
		var children = this.container.node().childNodes;
		var count = children.length;
		for (var i = count - 1 ; i >= 0 ; i--) {
			// removing children[i] crashes in iPad, selecting the node with d3 and removing it works!!
			d3.select(children[i]).remove();
		}
	},

	emptyAndBuild: function () {
		this.emptyGraph();
		this.build();
	},

	clearRangeSelection: function () {
		this.rangeSelection = [null, null];
		this.selectionLayer
			.attr("width", 0); // Hides selection layer
	},

	inRangeSelection: function () {
		return (this.rangeSelection[0] != null && this.rangeSelection[1] == null);
	},

	updateShownData: function () {
		if (this.rangeSelection[0] != this.rangeSelection[1]) {
			if (this.rangeSelection[0] > this.rangeSelection[1]) {
				this.rangeSelection.reverse();
			}
			var dataLen = this.shownData.length;
			for ( var i = 0 ; i < dataLen ; ++i ) {
				this.shownData[i].values = this.shownData[i].values.slice(this.rangeSelection[0], this.rangeSelection[1] + 1);
			}
			this.emptyAndBuild();
		}
		this.clearRangeSelection();
		return;
	},

	clearSelection: function () {
		this.shownDataDeepCopy();
		this.emptyAndBuild();
	},

	shownDataDeepCopy: function () {
		this.shownData = $.extend(true, [], this.cachedData); // Deep copy by value
		this.changeDataFrequency();
	},

	defaultDotStyle: function () {
		return {'radius': 4, 'stroke': '2px', 'hlRadius': 5, 'hlStroke': '0px'};
	},

	setDotStyle: function () {
		var pointSpan = 20;

		switch (this.chartType) {
			case 'stackArea':
				this.dotStyle = {'radius': 0, 'stroke': '0px', 'hlRadius': 3, 'hlStroke': '2px'};
				break;
			default:
				this.dotStyle =  ( this.width / pointSpan > this.numPoints )
					? this.defaultDotStyle()
					: {'radius': 0, 'stroke': '0px', 'hlRadius': 4, 'hlStroke': '2px'};
				break;
		}
	},

	changeDataFrequency: function () {

		var size = Math.floor(this.chartOptions.frequency / this.baseFrequency);

		if ( size <= 1 ) {
			return;
		}

		var newValues = [];
		var numSeries = this.shownData.length;
		var len = this.shownData[0].values.length;

		for ( var i = 0 ; i < numSeries ; i++ ) {
			newValues.push([]);
		}

		for ( var i = (len - 1) % size ; i < len - size ; i = i + size ) {
			for ( var serie = 0 ; serie < numSeries ; serie++ ) {
				newValues[serie].push({
					x: this.shownData[serie].values[i+size].x,
					//y: d3.mean(this.shownData[serie].values.slice(i,i+size), function (d) { return d.y; })
					y: this.shownData[serie].values[i+size].y
				});
			}
		}

		for ( var i = 0 ; i < numSeries ; i++ ) {
			newValues.push([]);
			this.shownData[i].values = newValues[i];
		}
	},

	changeChartFrequency: function (freq) {
		this.chartOptions.frequency = freq;
		this.shownDataDeepCopy();
		this.emptyAndBuild();
	},

}

var BitoConverter = {
	$el: null,
	$input: null,
	$values: {buy: null, sell: null},
	$date: null,
	values: null,
	timeout: {keyup: null},

	init: function (element, values) {
		this.$el = element;
		this.$date = this.$el.find('h3 span');
		this.$input = this.$el.find('[name=base]');

		var cachedValue = this.getCachedValue();
		if (cachedValue) {
			this.$input.val(cachedValue);
		}

		this.values = values;

		var refdate = moment(this.values.datetime);
		this.$date.html(refdate.format('ddd, MMM D, <b>HH:mm</b>'));

		this.updateValues();

		this.$input.on('keyup', $.proxy(this.inputKeyup, this));
		this.$input.on('click', $.proxy(this.inputFocus, this));
		this.$input.on('blur', $.proxy(this.inputBlur, this));
	},

	updateValues: function () {
		var base = +this.$input.val();

		if ( base == 0 || isNaN(base) ) {
			base = 1;
		}

		this.$el.find('.unity-values .buy span.price').text( numberFormat( this.values.buy, 2 ) );
		this.$el.find('.unity-values .sell span.price').text( numberFormat( this.values.sell, 2 ) );
		this.$el.find('.unity-values .diff span.price').text( numberFormat( this.values.buy - this.values.sell, 2 ) );
		this.$el.find('.values .buy span.price').text( numberFormat( base * this.values.buy, 2 ) );
		this.$el.find('.values .sell span.price').text( numberFormat( base * this.values.sell, 2 ) );
	},

	inputKeyup: function () {
		var that = this;
		window.clearTimeout(this.timeout.keyup);
		this.timeout.keyup = setTimeout(function () {
			that.updateValues();
		}, 350);
	},

	inputFocus: function () {
		this.$input.select();
	},

	inputBlur: function () {
		var value = null;
		if (this.$input.val()) {
			value = numberFormat(this.$input.val(), 7);
			this.$input.val(value);
		}
		this.setCachedValue(value);
	},

	getCachedValue: function () {
		var value = null;
		if (localStorage) {
			value = localStorage.getItem('converter-value');
		}
		return value;
	},

	setCachedValue: function (val) {
		if (localStorage) {
			(val)
				? localStorage.setItem('converter-value', val)
				: localStorage.removeItem('converter-value');
		}
	},
}

var svgChart = null;

var chart = {
	getData: function () {
		$.ajax({
			type: 'POST',
			url: 'http://bitcoinprice.today/ajax/bitonic',
			dataType: 'json',
			data: {
				'start-day': $('[name="start-day"]').val(),
				'end-day': $('[name="end-day"]').val(),
				'chart-market': $('[name="chart-market"]').val(),
			},
			success: function (response) {
				svgChart.update({
					chartType: response.type,
					data: response.data
				});
			}
		});
	}
}

var svgChart = null;

$(document).ready(function(){
	svgChart = TWCAdvancedChart;

	var $chart = $('#chart').attr('class', 'chart');

	svgChart.init({
		chartType: view.chartData.type,
		data: view.chartData.data,
		parent: $chart,
		boxSize: {'width': $chart.width(), 'height': 300},
		tooltip: $chart.find('.chart-tooltip'),
		chartOptions: view.chartData.options
	});

	var len = view.chartData.data.values.length;
	var lastValue = {
		datetime: view.chartData.data.values[len-1][0],
		buy: view.chartData.data.values[len-1][1][0],
		sell: view.chartData.data.values[len-1][1][1],
	};
	BitoConverter.init($('#converter'), lastValue);

	var freqs = [['5 min', 5], ['10 min', 10], ['30 min', 30], ['1 hr', 60], ['2 hr', 120], ['6 hr', 360],
		['12 hr', 720], ['1 day', 1440], ['2 day', 2880]];
	$.each(freqs, function (idx, freq) {
		$('<button>').attr('class', 'btn btn-xs').text(freq[0])
			.on('click', function () { svgChart.changeChartFrequency(freq[1]) })
			.appendTo($("#frequencies"));
	});

	$('#update-chart').on('click', function () {
		chart.getData();
	});

	$('#chart-market').on('change', function () {
		chart.getData();
	});

	$('#qr-thumbnail').on('click', function () {
		$('#qr-modal').modal('toggle')
	});
});
