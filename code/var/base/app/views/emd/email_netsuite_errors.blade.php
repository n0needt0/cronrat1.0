<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>

<h2>The following errors found in EMDs</h2>
<pre>
Invoice : Error
@foreach ($errors as $error)
  {{$error['invoice_number']}} : {{$error['error']}}<br/>
@endforeach
</pre>

++++++++++++++++++++++++++++
		<p>Thank you, <br />
			~The Admin Team</p>
	</body>
</html>