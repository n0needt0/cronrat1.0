@extends('layouts.jquerymobile') {{-- Web site Title --}}
@section('title') Cronrat @stop {{-- Content --}}
@section('runtimejs')

<script>

$(document).on("pagecreate", function (e) {

    $( ".popupRat" ).bind({

        popupafterclose: function(event, ui) {
            setTimeout(function(){$('#info').html('');},15000);
            }
     });

    $(".ratId").on("click",function(){
        $(".popupRat").popup("close");
        var cid = $(this).attr("id");
        $('#info').html($(this).attr("ref"));
        $("#actor").val(cid);
        debug(cid)
    });

    $(".ratDelete").bind("click", function(){

        $(".popupRat").popup("close");
        try{
                   $('#action').val('delete');
                   $('#update').submit();
              }catch (err){
                    debug(err);
                    alert(err.message);
             }
    });


	    //REMOVE: link disabled elements if not pro ://REMOVE
	    @if (empty($pro))
        @endif
});
</script>
@stop
@section('content')

<div id='info' class="ui-body alert-info">
@if(empty($rats))
<h4>Where are my rats?</h4>
<h4>To test installation click <a href=" {{Config::get('app.url')}}/r/{{$cronrat_code}}?rat=My Test Rat" target="_new" > {{Config::get('app.url')}}/r/{{$cronrat_code}}?rat=My Test Rat</a></h4>
<h4>Note the URL and make sure page displays OK, then refresh this page</h4>
<h4>read FAQ on how to use Cronrat</h4>
@endif
</div>


<div id="addCronrat" data-role="collapsible-set" data-theme="a" data-content-theme="a">

	@if($errors->has('error'))
	<div class="alert alert-danger">{{$errors->first('err')}}</div>
	@endif
  <?php
  date_default_timezone_set('UTC');
  ?>
	<div data-role="collapsible" data-collapsed="false">
		<h2>My Cron Rats as of : {{date('m/d h:i T',time())}}</h2>
        <ul id="ratlist" data-role="listview" >

        @foreach ($rats as $rat)
        <li>
        <a href="#popupRat" data-rel="popup" data-transition="slideup" class="ratId" id="{{ $rat['cronrat_code'] }}"
        ref="{{ Config::get('app.url')}}/r/{{ str_replace('::::','?RAT=',$rat['cronrat_code'])}}&CRONTAB={{ $rat['crontab']}}{{(empty($rat['email']))?'':'&EMAILTO='.$rat['email']}}{{ (empty($rat['url']))?'':'&URLTO='. urlencode($rat['url'])}}{{ (empty($rat['toutc']))?'':'&TOUTC='. urlencode($rat['toutc'])}}"> <img  id="{{ $rat['cronrat_code'] }}_img" src="/assets/images/{{ $rat['active'] }}.png" alt="ok" class="ui-li-icon ui-corner-none"/>
        as of {{ date('m/d H:i T', $rat['ts']) }} |
        {{ (empty($rat['nextrun']))?'': 'next run ' . date('m/d H:i T', $rat['nextrun']) }} | {{ $rat['cronrat_name'] }}</a></li>
        @endforeach
		</ul>
	</div>

</div>

<div data-role="popup" id="popupRat" class="popupRat" data-theme="b">
<a href="#" data-rel="back" data-role="button" data-theme="b" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
        </br>
        <ul data-role="listview" data-inset="true" style="min-width:210px;">
            <li><a href="#" class="ratDelete">Delete Rat</a></li>
        </ul>
</div>

<form id='update' action="{{ URL::to('cronrat/update') }}" method="post" data-ajax="false">
<input id='url' name='url' type='hidden' value='/cronrat'>
<input id='action' name='action' type='hidden'>
<input id='actor' name='actor' type='hidden'>
<input id='success' name='success' type='hidden'>
<input id='error' name='error' type='hidden'>
</form>
@stop
