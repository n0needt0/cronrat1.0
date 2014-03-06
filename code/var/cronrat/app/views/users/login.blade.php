@extends('layouts.jquerymobile')
{{-- Web site Title --}}
@section('title')
Log In

@stop

{{-- Content --}}
@section('content')

<div data-role="controlgroup" data-type="horizontal" align='center'>
    <a href="{{ URL::to('users/register') }}" data-rel="dialog" data-role="button">Register Free up to 50 cron monitors (contact us for more)</a>
</div>

<h3>Login</h3>
@if(Config::get('authentication.radius') == true )
<div class="ui-body alert-info">
If you use email login, use password, otherwise use RSA and token...
</div>
@endif
<div class="well">

<form action="{{ URL::to('users/login') }}" method="post" data-ajax="false">
    {{ Form::token(); }}
     <fieldset>
         <input name="email" id="email" value="{{ Request::old('email') }}" type="text" class="form-control" placeholder="E-mail or User Name">
         @if($errors->has('email'))
            <div class="alert alert-danger"> {{$errors->first('email')}}</div>
         @endif
         @if(Config::get('authentication.radius') == true )
         <input name='password' type="password" placeholder="Password or RSA Code" class="form-control">
         @endif

         @if(Config::get('authentication.radius') == false )
         <input name='password' type="password" placeholder="Password" class="form-control">
         @endif

         @if($errors->has('password'))
         <div class="alert alert-danger">{{$errors->first('password')}}</div>
         @endif
         <input type="submit" data-theme="{{Config::get('app.jqm_theme')}}" value="Log In">
         </br>
     </fieldset>
     <div data-role="controlgroup" data-type="horizontal" align='center'>
         <input type="checkbox" name="rememberMe" id="rememberMe" value="1"/>
	     <label for="rememberMe" class="ui-btn ui-mini">auto login</label>
		 <a href="{{ URL::to('users/resetpassword') }}" data-rel="dialog" data-role="button">lost password?</a>
     </div>
</form>
</div>
@stop