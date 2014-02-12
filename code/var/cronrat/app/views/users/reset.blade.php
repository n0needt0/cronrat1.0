@extends('layouts.jquerymobile')

{{-- Web site Title --}}
@section('title')
@parent
Reset Password
@stop

{{-- Content --}}
@section('content')
    <div data-theme="{{Config::get('app.jqm_theme')}}" >
        <h3>Reset Password</h3>
    	<form class="form-horizontal" action="{{ URL::to('users/resetpassword') }}" method="post" data-ajax="false">
        	{{ Form::token() }}

    		<div class="control-group {{ ($errors->has('email') ? 'error' : '') }}" for="email">
                <div class="controls">
                    <input name="email" id="email" value="{{ Request::old('email') }}" type="text" class="form-control" placeholder="Enter E-mail">
                    @if($errors->has('email'))
                        <div class="alert alert-danger"> {{$errors->first('email')}}</div>
                    @endif
                </div>
            </div>

        	<div class="form-actions top-buffer">
        	    <input type="submit" data-theme="{{Config::get('app.jqm_theme')}}" value="Reset Password"> </br>
        	</div>
        </form>
    </div>
@stop