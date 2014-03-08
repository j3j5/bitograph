
var accList = angular.module('AccountList', [])
.controller('MainChartController', function($scope, $element) {

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