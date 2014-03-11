<!DOCTYPE html>
<html>
	<head>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
	{{ Asset::container('head')->styles() }}
		<title></title>
	</head>

	<body>
		{{ Form::open('register') }}
			<!-- username field -->
			<p>{{ Form::label('username', 'Username') }}</p>
			<p>{{ Form::text('username') }}</p>
			<!-- password field -->
			<p>{{ Form::label('password', 'Password') }}</p>
			<p>{{ Form::password('password') }}</p>
			<!-- repeat pass field -->
			<p>{{ Form::label('password2', 'Repeat password') }}</p>
			<p>{{ Form::password('password2') }}</p>
			<!-- repeat pass field -->
			<p>{{ Form::label('email', 'Your email [optional]') }}</p>
			<p>{{ Form::email('email') }}</p>
			<!-- submit button -->
			<p>{{ Form::submit('Register', array('class' => 'btn btn-primary')) }}</p>
		{{ Form::close() }}
	</body>
</html>
