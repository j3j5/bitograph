<!DOCTYPE html>
<html>
	<head>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
	{{ Asset::container('head')->styles() }}
		<title></title>
	</head>

	<body>
		{{ Form::open('login') }}
			<!-- username field -->
			<p>{{ Form::label('username', 'Username') }}</p>
			<p>{{ Form::text('username') }}</p>
			<!-- password field -->
			<p>{{ Form::label('password', 'Password') }}</p>
			<p>{{ Form::password('password') }}</p>
			<!-- submit button -->
			<p>{{ Form::submit('Login', array('class' => 'btn btn-primary')) }}</p>
		{{ Form::close() }}
	</body>
</html>
