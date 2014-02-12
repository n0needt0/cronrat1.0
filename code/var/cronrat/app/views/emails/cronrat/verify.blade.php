<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Please verify Cronrat Url</h2>

		<p><b>Cronrat: {{{ $cronrat_name }}} to {{{ $fail_email }}}</p>
		<p>To activate your cronrat url, <a href="{{  URL::to('verify/cronrat') . '/' .  urlencode($verify) }}">click here.</a></p>
		<p>Or point your browser to this address: <br /> {{  URL::to('cronrat/activate') . '/' .  urlencode($verify) }}</p>

		<p>Once verified, you can use this url...blah </p>
		<p>Thank you, <br />
			~The Cronrat Team</p>
	</body>
</html>