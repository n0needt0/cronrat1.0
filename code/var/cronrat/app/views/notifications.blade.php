@if ($message = Session::get('success'))
<div class="ui-body alert-success">
	{{ $message }}
</div>
@endif

@if ($message = Session::get('error'))
<div class="ui-body alert-danger">
	        {{ $message }}
</div>
@endif

@if ($message = Session::get('warning'))
<div class="ui-body alert-warning">
	{{ $message }}
</div>
@endif

@if ($message = Session::get('info'))
<div class="ui-body alert-info">
	{{ $message }}
</div>
@endif
