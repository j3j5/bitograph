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
			<div class="col-xs-12">
				<div id="chart">
					<svg></svg>
					<div class="chart-tooltip"></div>
				</div>
			</div>
			<div class="container">
				<div id="frequencies"></div>
			</div>
		</div>

		<div class="container">
			<div class="col-sm-6 col-md-4">
				<div id="converter">
					<h3>On <span></span></h3>
					<div class="input-group">
						<span class="input-group-addon">฿</span>
						<input class="form-control" name="base" placeholder="1.0000">
					</div>
					<div class="values">
						<div class="value buy"></div>
						<div class="value sell"></div>
					</div>
				</div>
			</div>
		</div>

	</section>

	</body>
	<script>
		var view = {
			'chartData': {{ $data }}
		};
	</script>
	{{ Asset::container('footer')->scripts() }}
</html>
