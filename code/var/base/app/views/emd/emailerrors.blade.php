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
<h2>Accepted service codes are:</h2>
<pre>
@foreach ($valid_services as $service)
{{$service}}<br/>
@endforeach
</pre>
Anything else should be dsignated as Other

		<p>Thank you, <br />
			~The Admin Team</p>
	</body>
</html>