<!DOCTYPE html>
<html>
	<head>
	{{ Asset::container('head')->styles() }}
	{{ Asset::container('head')->scripts() }}
		<title></title>
	</head>

	<body>

	<div id="chart">
		<svg></svg>
		<div class="tooltip"></div>
	</div>

	</body>
	<script>
		var view = {
			'chartData': {{ $data }}
		};
	</script>
	{{ Asset::container('footer')->scripts() }}
</html>
