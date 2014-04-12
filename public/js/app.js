
var accList = angular.module('AccountList', [])
.service('StateProv', function () {
	// https://variadic.me/posts/2013-10-15-share-state-between-controllers-in-angularjs.html
	'use strict';

	var state = {
		startDate:   BCPT.view.parameters.startDate,
		endDate:     BCPT.view.parameters.endDate,
		minDateData: BCPT.view.parameters.calendarMinDate,
		maxDateData: BCPT.view.parameters.calendarMaxDate,
		market:      BCPT.view.parameters.market
	};

	return {
		state: state,
	};
})
.controller('MetricsController', function($scope, StateProv) {

	$scope.state = StateProv.state;

	var today = BCPT.view.parameters.endDate;
	var dateFormat = 'YYYY-MM-DD';
	$scope.data = {
		markets:   ['bitonic', 'bitpay'],
		ranges: [
			{label: 'today', start: today, end: today},
			{label: 'last 2 days', start: moment(today).subtract('days', 1).format(dateFormat), end: today},
			{label: 'last week', start: moment(today).subtract('days', 7).format(dateFormat), end: today},
			{label: 'last month', start: moment(today).subtract('months', 1).format(dateFormat), end: today},
		]
	};

	$scope.setDates = function (start, end) {
		$scope.state.startDate = start;
		$scope.state.endDate = end;
	}

})
.controller('MainChartController', function($scope, StateProv, $http, $element) {

	$scope.state = StateProv.state;
	$scope.isLoading = false;

	$scope.$watchCollection('state', function (newVal, oldVal) {
		if ( newVal == oldVal ) { return; } // No changes no fetching
		$scope.fetchChartData();
	});

	$scope.frequency = ( !BCPT.view.chartData.options.hasOwnProperty('frequency') )
		? 5 // 5 min
		: BCPT.view.chartData.options.frequency;

	$scope.chart = BCPTChart;

	$scope.init = function () {

		var $chart = $element[0].querySelectorAll('.chart')[0];

		$scope.chart.init({
			chartType: BCPT.view.chartData.type,
			data: BCPT.view.chartData.data,
			parent: $chart,
			boxSize: {'width': $chart.offsetWidth, 'height': 300},
			tooltip: $element[0].querySelectorAll('.chart-tooltip')[0], //$chart.find('.chart-tooltip'),
			chartOptions: BCPT.view.chartData.options,
		});
	}

	$scope.fetchChartData = function () {
		$scope.isLoading = true;
		$http({
			url: BCPT.view.HOST + '/ajax/bitonic',
			method: 'POST',
			data: 'start-day=' + $scope.state.startDate + '&end-day=' + $scope.state.endDate + '&chart-market=' + $scope.state.market,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		})
		.success(function(response, status, headers, config) {
			$scope.isLoading = false;
			$scope.updateChart(response);
		}).error(function(data, status, headers, config) {
			$scope.isLoading = false;
			console.log('error');
		});
	}

	$scope.updateChart = function (chartData) {
		$scope.chart.update({
			chartType: chartData.type,
			data: chartData.data
		});
	}

	$scope.freqs = [['5 min', 5], ['10 min', 10], ['30 min', 30], ['1 hr', 60], ['2 hr', 120], ['6 hr', 360],
		['12 hr', 720], ['1 day', 1440], ['2 day', 2880]];

	$scope.chageFrequency = function (freq) {
		$scope.frequency = freq;
		$scope.chart.changeChartFrequency(freq);
	}

})
.controller('ConverterController', function($scope) {

		var lastValues = BCPT.view.chartData.data.values[ BCPT.view.chartData.data.values.length-1 ];

		$scope.lastValue = {
			datetime: lastValues[0],
			buy: lastValues[1][0],
			sell: lastValues[1][1],
		};


})
.controller('AccountListController', function($scope, $filter) {

	$scope.purchases = [
	];

	var lastValues = BCPT.view.chartData.data.values[ BCPT.view.chartData.data.values.length-1 ]
	$scope.markets = [
		{name: 'Bitonic', buys: lastValues[1][0], sells: lastValues[1][1]},
		{name: 'Bitpay',  buys: null,   sells: 487.87},
	];
	$scope.market = $scope.markets[0];

	$scope.getTotals = function () {
		var totals = {
			btc: 0,
			cur: 0
		};
		angular.forEach(this.purchases, function (purchase) {
			totals.btc += purchase.btc;
			totals.cur += purchase.cur;
		});
		return totals;
	},

	$scope.addNew = function (newAcc) {

		if (!newAcc) { return; }

		if (!newAcc.btc) {
			return;
		}
		if (!newAcc.cur) {
			return;
		}

		$scope.purchases.push({
			date: newAcc.date || null,
			btc:  +newAcc.btc,
			cur:  +newAcc.cur
		});
		newAcc.date = '';
		newAcc.btc  = '';
		newAcc.cur  = '';
	}

});

accList.config(function($interpolateProvider) {
	$interpolateProvider.startSymbol('{%');
	$interpolateProvider.endSymbol('%}');
});

//http://codepen.io/adamesque/pen/qHJsf
var w = 500,
	h = 100;

var vertices = d3.range(80).map(function(d) {
	return [Math.random() * w, Math.random() * h];
});

var delaunay = d3.geom.delaunay(vertices);

var svg = d3.select("#header-net")
	.append("svg")
	.attr("preserveAspectRatio", "xMidYMid slice")
	.attr("viewBox", [0, 0, w, h].join(' '))

var meshBgGradient = svg.append("svg:defs")
	.append("svg:linearGradient")
	.attr("id", "meshBgGradient")
	.attr("x1", "0%")
	.attr("y1", "0%")
	.attr("x2", "100%")
	.attr("y2", "0%")
	.attr("spreadMethod", "pad");

meshBgGradient.append("svg:stop")
	.attr("offset", "0%")
	.attr("stop-color", "#f6f6f6");
meshBgGradient.append("svg:stop")
	.attr("offset", "100%")
	.attr("stop-color", "#d0d0d0");

svg.append("rect")
	.style("fill", "url(#meshBgGradient)")
	.attr("width", "100%")
	.attr("height", "100%");

svg.append("g")
	.selectAll("path")
	.data(delaunay)
	.enter().append("path")
	.attr("class", function(d, i) { return "q" + (i % 9) + "-9"; })
	.attr("d", function(d) { return "M" + d.join("L") + "Z"; });