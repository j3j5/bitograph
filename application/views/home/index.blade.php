<!DOCTYPE html>
<html>
<head>
	<title>Bitcoin Price TODAY!</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	{{ Asset::container('head')->styles() }}
	@include('partials.analytics')
</head>

<body>

<div id="header-net">
</div>

<header class="container">
	<a href="/" class="brand">
		<span class="bold-text">BITCOIN</span><span class="light-text">PRICE</span>TODAY!
	</a>
	<!-- a href="/login" class="pull-right">Login</a -->
</header>

<section ng-app="AccountList" class="main">
	<div ng-controller="MetricsController" id="main-metrics" class="container">
		<span class="tag">Market</span>
		<div class="market-selector" ng-init="marketActive=false;"
			ng-mouseenter="marketActive=true;" ng-mouseleave="marketActive=false;" ng-class="{'active': marketActive}">
			<span class="value">
				{%state.market%}
			</span>
			<ul class="options">
				<li class="opt" ng-repeat="market in data.markets" ng-click="state.market=market;" ng-show="state.market!=market">
					{% market %}
				</li>
			</ul>
		</div>
		<span class="tag">From/To</span>
		<div class="dates-selector" ng-init="datesActive=false;"
			ng-mouseenter="datesActive=true;" ng-mouseleave="datesActive=false;" ng-class="{'active': datesActive}">
			<span class="value">
				{% state.startDate | date:'MMM dd' %}
				<span class="small">{% state.startDate | date:'yyyy' %}</span> -
				{% state.endDate | date:'MMM dd' %}
				<span class="small">{% state.endDate | date:'yyyy' %}</span>
			</span>
			<ul class="options">
				<li class="opt" ng-repeat="range in data.ranges" ng-click="setDates(range.start, range.end);">
					{% range.label %}
				</li>
				<li>
					<input type="date" name="start-day"
						   class="input-sm"
						   ng-model="state.startDate"
						   value="{% state.startDate %}"
						   min="{% state.minDateData %}" max="{% state.maxDateData %}">
					<input type="date" name="end-day"
						   class="input-sm"
						   ng-model="state.endDate"
						   value="{% state.endDate %}"
						   min="{% state.minDateData %}" max="{% state.maxDateData %}" >
				</li>
			</ul>
		</div>
	</div>
	<div id="main-chart" ng-controller="MainChartController" ng-init="init()">
		<div class="chart">
			<svg></svg>
			<div class="chart-tooltip"></div>
		</div>
		<div id="frequencies" class="container">
			<span class="tag">Frequency</span>
			<ul>
				<li ng-repeat="freq in freqs" ng-click="chageFrequency(freq[1])" ng-class="{'selected': frequency == freq[1]}">
					{% freq[0] %}
				</li>
			</ul>
		</div>
		<div class="loading-mask" ng-show="isLoading">
			<span>Loading</span>
		</div>
	</div>
	<div class="container main-container">
		<div ng-controller="ConverterController" class="col-sm-6 col-md-6">
			<div id="converter">
				<h3 class="light-text">On <span>{% lastValue.datetime | date:'EEE, MMM dd, HH:mm' %}</span></h3>
				<div class="unity-values row">
					<div class="value ref col-xs-2"><span class="cur">฿</span> <span class="price">1</span></div>
					<div class="value buy col-xs-4"><span class="cur">&euro;</span> <span class="price">{% lastValue.buy %}</span></div>
					<div class="value sell col-xs-4"><span class="cur">&euro;</span> <span class="price">{% lastValue.sell %}</span></div>
					<div class="value diff col-xs-2"> <span class="price">{% lastValue.buy - lastValue.sell | number:2 %}</span></div>
				</div>
				<div class="row">
					<div class="input-group">
						<span class="input-group-addon">฿</span>
						<input ng-model="value" class="form-control" name="base" placeholder="1.0000">
					</div>
				</div>
				<div class="values row">
					<div class="value buy col-xs-6">
						<span class="cur">&euro;</span>
						<span class="price">{% lastValue.buy * (value || 1) | number:2 %}</span>
					</div>
					<div class="value sell col-xs-6">
						<span class="cur">&euro;</span>
						<span class="price">{% lastValue.sell * (value || 1) | number:2 %}</span>
					</div>
				</div>
			</div>
		</div>
		<div id="donations" class="col-xs-12 col-sm-5 col-sm-offset-1">
			<h3 class="light-text">
				We accept donations:
			</h3>
			<div>
				<b>฿</b>
				address
				<kbd>1FVFeaRvFtCxyTy7KzYKRnM92syDJpVe8p</kbd>
			</div>
			<div class="qr">
				<img id="qr-thumbnail" class="center-block" src="./img/donation/donation-qr-small.png">
			</div>
		</div>
		<!-- div ng-controller="AccountListController" class="account-list col-xs-12 col-sm-6">
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
		</div -->
	</div>
</section>


</body>
<script>
	var BCPT = {
		view: {
			'HOST': '{{ URL::base() }}',
			'parameters': {{ $view_params }},
			'chartData': {{ $chart_data }}
		}
	};
</script>
{{ Asset::container('footer')->scripts() }}
</html>