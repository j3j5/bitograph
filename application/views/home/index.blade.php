<!DOCTYPE html>
<html>
	<head>
	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-35249935-3', 'bitcoinprice.today');
		ga('send', 'pageview');
	</script>
	{{ Asset::container('head')->styles() }}
		<title>Bitcoin Price TODAY!</title>
	</head>

	<body>

	<header class="navbar navbar-static-top" role="banner">
		<div class="navbar-header">
			<a href="/" class="navbar-brand">Bitochart</a>
		</div>
		<!-- nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
			<ul class="nav navbar-nav">
				<li>
					<a href="/login">Login</a>
				</li>
			</ul>
		</nav -->
	</header>

	<section id="main">
		<div id="chart-box">
			<div class="container">
				Market:
				<select name="chart-market" id="chart-market">
					<option value="bitonic" selected="selected">Bitonic</option>
					<option value="bitpay">Bitpay</option>
				</select>
				Time:
				<input type="date" name="start-day"
					class="input-sm"
					value="{{ $chart_selector['start'] }}"
					min="{{ $chart_selector['min'] }}" max="{{ $chart_selector['max'] }}">
				<input type="date" name="end-day"
					class="input-sm"
					value="{{ $chart_selector['end'] }}"
					min="{{ $chart_selector['min'] }}" max="{{ $chart_selector['max'] }}" >
				<button id="update-chart" class="btn">Go</button>
			</div>
			<div class="col-xs-12">
				<div id="chart">
					<svg></svg>
					<div class="chart-tooltip"></div>
				</div>
			</div>
			<div class="container">
				<div id="frequencies">
					<span>Frequency: </span>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="col-sm-6 col-md-6">
				<div id="converter">
					<h3>On <span></span></h3>
					<div class="unity-values row">
						<div class="value ref col-xs-2"><span class="cur">฿</span> <span class="price">1</span></div>
						<div class="value buy col-xs-4"><span class="cur">&euro;</span> <span class="price"></span></div>
						<div class="value sell col-xs-4"><span class="cur">&euro;</span> <span class="price"></span></div>
						<div class="value diff col-xs-2"> <span class="price"></span></div>
					</div>
					<div class="row">
						<div class="input-group">
							<span class="input-group-addon">฿</span>
							<input class="form-control" name="base" placeholder="1.0000">
						</div>
					</div>
					<div class="values row">
						<div class="value buy col-xs-6"><span class="cur">&euro;</span> <span class="price"></span></div>
						<div class="value sell col-xs-6"><span class="cur">&euro;</span> <span class="price"></span></div>
					</div>
				</div>
			</div>
			<div id="donations" class="col-xs-5 col-xs-offset-1">
				<h3>We accept donations: </h3>
				<div>
					<b>฿</b>
					address
					<kbd>1FVFeaRvFtCxyTy7KzYKRnM92syDJpVe8p</kbd>
				</div>
				<div class="qr">
					<img id="qr-thumbnail" class="center-block" src="./img/donation/donation-qr-small.png">
				</div>
			</div>
		</div>

	</section>

	<div id="qr-modal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Donation</h4>
				</div>
				<div class="modal-body">
					<img class="img-responsive" src="./img/donation/donation-qr-big.png">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	</body>
	<script>
		var view = {
			'chartData': {{ $data }}
		};
	</script>
	{{ Asset::container('footer')->scripts() }}
</html>
