@extends('layouts.jquerymobile')

{{-- Web site Title --}}
@section('title')
@parent
Suspend User
@stop

{{-- Content --}}
@section('content')
<h3>Suspend {{ $user->email }}</h3>
<div class="well">
	<form class="form-horizontal" action="{{ URL::to('users/suspend') }}/{{ $user->id }}" method="post">
    	{{ Form::token() }}

		<div class="control-group {{ ($errors->has('suspendTime')) ? 'error' : '' }}" for="suspendTime">
            <label class="control-label" for="suspendTime">Duration</label>
            <div class="controls">
                <input name="suspendTime" id="suspendTime" value="{{ Request::old('suspendTime') }}" type="text" class="form-control" placeholder="Minutes">
                {{ ($errors->has('suspendTime') ? $errors->first('suspendTime') : '') }}
            </div>
        </div>

    	<div class="form-actions top-buffer">
    		<button data-theme="{{Config::get('app.jqm_theme')}}" type="submit">Suspend User</button>
    	</div>
  </form>
</div>

@stop