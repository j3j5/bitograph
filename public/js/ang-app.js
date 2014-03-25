
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
			url: 'http://javi.bitcoinprice.today/ajax/bitonic',
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
.controller('AccountListController', function($scope, $filter) {

	$scope.purchases = [
	];

	$scope.markets = [
		{name: 'Bitonic', buys: 498.61, sells: 481.15},
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

svg.append("g")
	.selectAll("path")
	.data(delaunay)
	.enter().append("path")
	.attr("class", function(d, i) { return "q" + (i % 9) + "-9"; })
	.attr("d", function(d) { return "M" + d.join("L") + "Z"; });