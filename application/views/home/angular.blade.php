<!DOCTYPE html>
<html>
<head>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
	{{ Asset::container('head')->styles() }}
	<title></title>
</head>

<body>

<header class="navbar navbar-static-top" role="banner">
	<div class="navbar-header">
		<a href="/" class="navbar-brand">Bitcoin price TODAY!</a>
	</div>
	<div id="header-net" style="position: absolute; top: 0px; right: 0px; height: 70px; width: 750px;">
	</div>
</header>

<section id="main" ng-app="AccountList">
	<div ng-controller="MainChartController" ng-init="init()">
		<div class="chart">
			<svg></svg>
			<div class="chart-tooltip"></div>
		</div>
	</div>
	<div class="container">
		<div ng-controller="AccountListController" class="col-xs-12 col-sm-6">
			<ul class="account-list">
				<li class="row">
					<div class="col-xs-3">Date</div>
					<div class="col-xs-3">BTC</div>
					<div class="col-xs-3">EUR</div>
					<div class="col-xs-3">EUR/BTC</div>
				</li>
				<li class="row purchase-row" ng-repeat="purchase in purchases">
					<div class="col-xs-3">{% purchase.date | date:'MMM dd, yyyy' %}</div>
					<div class="col-xs-3">{% purchase.btc | number:8 %}</div>
					<div class="col-xs-3">{% purchase.cur | number:2 %}</div>
					<div class="col-xs-3">{% purchase.cur/purchase.btc | number:2 %}</div>
					<div class="remove-row">
						<a ng-click="purchases.splice($index, 1)">&times;</a>
					</div>
				</li>
				<li class="row input-row">
					<div class="col-xs-3">
						<input type="date" class="input-date" name="accNew.date" ng-model="accNew.date" />
					</div>
					<div class="col-xs-3">
						<input type="text" name="accNew.btc" ng-model="accNew.btc" placeholder="BTC" />
					</div>
					<div class="col-xs-3">
						<input type="text" name="accNew.cur" ng-model="accNew.cur" placeholder="EUR" />
					</div>
					<div class="col-xs-3">
						<button ng-click="addNew(accNew)">Add</button>
					</div>
				</li>
				<li class="row total-row">
					<div class="col-xs-3">Total</div>
					<div class="col-xs-3">{% getTotals().btc | number:8 %}</div>
					<div class="col-xs-3">{% getTotals().cur | number:2 %}</div>
					<div class="col-xs-3">{% getTotals().cur/getTotals().btc | number:2 %}</div>
				</li>
				<li class="row market-row" ng-show="purchases.length > 0">
					<div class="col-xs-3">
						<select ng-model="market" ng-options="m.name for m in markets"></select>
					</div>
					<div class="col-xs-3">{% getTotals().btc | number:8 %}</div>
					<div class="col-xs-3">{% (getTotals().btc * market.sells) | number:2 %}</div>
					<div class="col-xs-3">{% market.sells | number:2 %}</div>
				</li>
				<li class="row difference-row" ng-show="purchases.length > 0">
					<div class="col-xs-offset-6 col-xs-3">{% (getTotals().btc * market.sells - getTotals().cur) | number:2 %}</div>
					<div class="col-xs-3">{% (getTotals().btc * market.sells - getTotals().cur) / getTotals().cur * 100 | number:2 %}%</div>
				</li>
			</ul>
		</div>
	</div>
</section>


</body>
<script>
	var view = {
		'HOST': '{{ URL::base() }}',
		'chartData': {{ $data }}
	};
</script>
{{ Asset::container('footer')->scripts() }}
</html>