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
		<a href="/" class="navbar-brand">
			<span class="bold-text">BITCOIN</span><span class="light-text">PRICE</span>TODAY!
		</a>
	</div>
	<div id="header-net" style="position: absolute; top: 0px; right: 0px; height: 70px; width: 750px;">
	</div>
</header>

<section id="main" ng-app="AccountList">
	<div ng-controller="MetricsController">
		Market:
		<select ng-model="data.market" ng-options="m for m in data.markets" name="chart-market"></select>
		Time:
		<input type="date" name="start-day"
			   class="input-sm"
			   value="{% data.startDate.selected %}"
			   min="{% data.startDate.min %}" max="{% data.startDate.max %}">
		<input type="date" name="end-day"
			   class="input-sm"
			   value="{% data.endDate.selected %}"
			   min="{% data.endDate.min %}" max="{% data.endDate.min %}" >
		<button id="update-chart" class="btn">Go</button>
	</div>
	<div ng-controller="MainChartController" ng-init="init()">
		<div class="chart">
			<svg></svg>
			<div class="chart-tooltip"></div>
		</div>
	</div>
	<div class="container main-container">
		<div ng-controller="AccountListController" class="account-list col-xs-12 col-sm-6">
			<h3 class="light-text">
				Your transactions
			</h3>
			<ul>
				<li class="row">
					<div class="col-date">Date</div>
					<div class="col-btc">BTC</div>
					<div class="col-currency">EUR</div>
					<div class="col-rate">EUR/BTC</div>
				</li>
				<li class="row purchase-row" ng-repeat="purchase in purchases">
					<div class="col-date light-text">{% purchase.date | date:'MMM dd, yyyy' %}</div>
					<div class="col-btc ms-text">{% purchase.btc | number:8 %}</div>
					<div class="col-currency ms-text">{% purchase.cur | number:2 %}</div>
					<div class="col-rate ms-text">{% purchase.cur/purchase.btc | number:2 %}</div>
					<div class="remove-row">
						<a ng-click="purchases.splice($index, 1)">&times;</a>
					</div>
				</li>
				<li class="row input-row">
					<div class="col-date">
						<input type="date" class="input-date" name="accNew.date" ng-model="accNew.date" />
					</div>
					<div class="col-btc">
						<input type="text" name="accNew.btc" ng-model="accNew.btc" placeholder="BTC" />
					</div>
					<div class="col-currency">
						<input type="text" name="accNew.cur" ng-model="accNew.cur" placeholder="EUR" />
					</div>
					<div class="col-rate">
						<button ng-click="addNew(accNew)">Add</button>
					</div>
				</li>
				<li class="row total-row">
					<div class="col-date ">Total</div>
					<div class="col-btc ms-text">{% getTotals().btc | number:8 %}</div>
					<div class="col-currency ms-text">{% getTotals().cur | number:2 %}</div>
					<div class="col-rate ms-text">{% getTotals().cur/getTotals().btc | number:2 %}</div>
				</li>
			</ul>
			<div class="summary">
				<select ng-model="market" ng-options="m.name for m in markets"></select>
				sells at <span class="ms-text">{% market.sells | number:2 %}</span>,
				your total BTCs <span class="ms-text">{% getTotals().btc | number:8 %}</span>
				are worth <span class="ms-text">{% (getTotals().btc * market.sells) | number:2 %}</span>.
				You have spent <span class="ms-text">&euro;{% getTotals().cur | number:2 %}</span>
				which makes a difference of <span class="ms-text">{% (getTotals().btc * market.sells - getTotals().cur) | number:2 %}</span>
				({% (getTotals().btc * market.sells - getTotals().cur) / getTotals().cur * 100 | number:2 %}%)
			</div>
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