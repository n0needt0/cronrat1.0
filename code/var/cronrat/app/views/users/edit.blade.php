@extends('layouts.jquerymobile')

{{-- Web site Title --}}
@section('title')
Cronrat::Edit Profile
@stop

{{-- Content --}}
@section('content')

<div id='info' class="ui-body alert-info">
    <li>Your Cronrat Code is: <b>{{ Sentry::getUser()->cronrat_code }}</b></li>
    <li>Cronrat Plan: <b>{{ $plan_name }}</b></li>
    <li>Live Cronrats: <b>{{ count($ratsused) }} of {{ $ratlimit }}, available {{ ($ratlimit - count($ratsused)) }}</b></li>
    <li>Job Frequency Limit: <b>{{$ttlmin}}min</b></li>
    <li>Url Pull on failure: <b>{{ ($urlto)? 'Enabled' : 'Disable'}}</b></li>
    <li>Alternative Email Address on failure: <b>{{ ($emailto)? 'Enabled' : 'Disable'}}</b></li>

</div>
<h3>Change Password</h3>
<div class="well">
	<form class="form-horizontal" action="{{ URL::to('users/changepassword') }}/{{ $user->id }}" method="post">
        {{ Form::token() }}

        <div class="control-group {{ $errors->has('newPassword') ? 'error' : '' }}" for="newPassword">
        	<label class="control-label" for="newPassword">New Password</label>
    		<div class="controls">
				<input name="newPassword" value="" type="password" class="form-control" placeholder="New Password">
    			{{ ($errors->has('newPassword') ?  $errors->first('newPassword') : '') }}
    		</div>
    	</div>

    	<div class="control-group {{ $errors->has('newPassword_confirmation') ? 'error' : '' }}" for="newPassword_confirmation">
        	<label class="control-label" for="newPassword_confirmation">Confirm New Password</label>
    		<div class="controls">
				<input name="newPassword_confirmation" value="" type="password" class="form-control" placeholder="New Password Again">
    			{{ ($errors->has('newPassword_confirmation') ? $errors->first('newPassword_confirmation') : '') }}
    		</div>
    	</div>

	    <div class="form-actions top-buffer">
	    	<input class="btn-primary btn" type="submit" value="Change Password" data-theme="{{Config::get('app.jqm_theme')}}">
	    	<input class="btn-inverse btn" type="reset" value="Reset" data-theme="{{Config::get('app.jqm_theme_alt')}}">
	    </div>
      </form>
  </div>

@if (Sentry::check() && Sentry::getUser()->hasAccess('admin'))
<h3>User Group Memberships</h3>
<div class="well">
    <form class="form-horizontal" action="{{ URL::to('users/updatememberships') }}/{{ $user->id }}" method="post">
        {{ Form::token() }}

        <table class="table">
            <thead>
                <th>Group</th>
                <th>Membership Status</th>
            </thead>
            <tbody>
                @foreach ($allGroups as $group)
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>
                            <div class="switch" data-on-label=" In" data-on='info' data-off-label=" Out">
                                <input name="permissions[{{ $group->id }}]" type="checkbox" {{ ( $user->inGroup($group)) ? 'checked' : '' }} >
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="form-actions top-buffer">
            <input class="btn-primary btn" type="submit" value="Update Memberships">
        </div>
    </form>
</div>
@endif

@stop