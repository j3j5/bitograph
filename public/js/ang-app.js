
var accList = angular.module('AccountList', [])
.service('StateProv', function () {
	// https://variadic.me/posts/2013-10-15-share-state-between-controllers-in-angularjs.html
	'use strict';

	var today = '2014-03-23';

	var state = {
		startDate:   '2014-03-16',
		endDate:     today,
		minDateData: '2014-01-30',
		maxDateData: today,
		market: 'bitonic'
	};

	return {
		state: state,
	};
})
.controller('MetricsController', function($scope, StateProv) {

	$scope.state = StateProv.state;

	$scope.data = {
		markets:   ['bitonic', 'bitpay']
	};

})
.controller('MainChartController', function($scope, StateProv, $http, $element) {

	$scope.state = StateProv.state;

	$scope.$watchCollection('state', function (newVal, oldVal) {
		$scope.fetchChartData();
	});

	$scope.chart = BCPTChart;

	$scope.init = function () {

		var $chart = $element[0].querySelectorAll('.chart')[0];

		$scope.chart.init({
			chartType: view.chartData.type,
			data: view.chartData.data,
			parent: $chart,
			boxSize: {'width': $chart.offsetWidth, 'height': 300},
			tooltip: $element[0].querySelectorAll('.chart-tooltip')[0], //$chart.find('.chart-tooltip'),
			chartOptions: view.chartData.options
		});
	}

	$scope.fetchChartData = function () {
		$http({
			url: view.HOST + '/ajax/bitonic',
			method: 'POST',
			data: 'start-day=' + $scope.state.startDate + '&end-day=' + $scope.state.endDate + '&chart-market=' + $scope.state.market,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		})
		.success(function(response, status, headers, config) {
			$scope.updateChart(response);
		}).error(function(data, status, headers, config) {
			console.log('error');
		});
	}

	$scope.updateChart = function (chartData) {
		$scope.chart.update({
			chartType: chartData.type,
			data: chartData.data
		});
	}
})
.controller('ConverterController', function($scope) {

		var len = view.chartData.data.values.length;

		$scope.lastValue = {
			datetime: view.chartData.data.values[len-1][0],
			buy: view.chartData.data.values[len-1][1][0],
			sell: view.chartData.data.values[len-1][1][1],
		};


})
.controller('AccountListController', function($scope, $filter) {

	$scope.purchases = [
	];

	var len = view.chartData.data.values.length;
	$scope.markets = [
		{name: 'Bitonic', buys: view.chartData.data.values[len-1][1][0], sells: view.chartData.data.values[len-1][1][1]},
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