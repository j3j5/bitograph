<!DOCTYPE html>
<html>
	<head>
	{{ Asset::container('head')->styles() }}
		<title></title>
	</head>

	<body>

	<div id="chart">
		<svg></svg>
		<div class="chart-tooltip"></div>
	</div>

	</body>
	<script>
		var view = {
			'chartData': {{ $data }}
		};
	</script>
	{{ Asset::container('footer')->scripts() }}
</html>
