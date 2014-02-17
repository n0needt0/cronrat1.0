@extends('layouts.jquerymobile')

{{-- Web site Title --}}
@section('title')
@parent
Register
@stop

{{-- Content --}}
@section('content')

<h3>Register New Account</h3>

<div class="well">
	<form class="form-horizontal" action="{{ URL::to('users/register') }}" method="post">
        {{ Form::token() }}

        <div class="control-group {{ ($errors->has('email')) ? 'error' : '' }}" for="email">
            <div class="controls">
                <input name="email" id="email" value="{{ Request::old('email') }}" type="text" class="form-control" placeholder="E-mail">
                @if ($errors->has('email'))
    			<div id='info' class="alert alert-danger">
    			{{ $errors->first('email') }}
    			</div>
    			@endif
            </div>
        </div>

		<div class="control-group {{ $errors->has('password') ? 'error' : '' }}" for="password">
    		<div class="controls">
				<input name="password" value="" type="password" class="form-control" placeholder="New Password">
				@if ($errors->has('password'))
    			<div id='info' class="alert alert-danger">
    			{{ $errors->first('password') }}
    			</div>
    			@endif
    		</div>
    	</div>

    	<div class="control-group {{ $errors->has('password_confirmation') ? 'error' : '' }}" for="password_confirmation">
    		<div class="controls">
				<input name="password_confirmation" value="" type="password" class="form-control" placeholder="New Password Again">
    			@if ($errors->has('password_confirmation'))
    			<div id='info' class="alert alert-danger">
    			{{ $errors->first('password_confirmation') }}
    			</div>
    			@endif
    		</div>
    	</div>

		<div class="form-actions top-buffer">
	    	<input data-theme="{{Config::get('app.jqm_theme')}}" type="submit" value="Register">
	    </div>
	</form>
</div>


@stop