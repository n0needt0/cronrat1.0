@extends('layouts.jquerymobile') {{-- Web site Title --}}
@section('title') Home Base @stop {{-- Content --}}
@section('runtimejs')

<script>
$.mobile.ajaxEnabled = false;

$.ajaxSetup ({
 // Disable caching of AJAX responses
 cache: false
 });

$(document).on("pagecreate", function (e) {

    var myselect = $("select#ttl");
    myselect[0].selectedIndex = 0;
    myselect.selectmenu("refresh");

    $("#ratlist").listview('refresh');

    $(".ratUrl").bind("click",function(){
        $(".popupRat").popup("close");
        var cid =   $("#cid").val();
        $("#info").html("<center>http(s)://cronrat.com/r/"+cid+"</center>");
        });

    $(".ratId").on("click",function(){
        $(".popupRat").popup("close");
        var cid =$(this).attr("id");
        $("#cid").val(cid);
        debug(cid)
    });

    $(".ratToggle").bind("click", function(){
        $(".popupRat").popup("close");
        try{
            var cid =   $("#cid").val();
            debug(cid);
            $('#update #action').val('toggle');
            $('#update #actor').val(cid);
            $('#update').submit();
         }catch (err){
             debug(err);
             alert(err.message)
         }
    });

    $(".ratDelete").bind("click", function(){
        $(".popupRat").popup("close");
        try{
                   var cid =   $("#cid").val();
                   debug(cid);
                   $('#action').val('delete');
                   $('#actor').val(cid);
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

@if (empty($pro))
    <div id='info' class="ui-body alert-info"><center><a href="/cronrat/upgrade">upgrade to full version</a></center></div>
@else
    <div id='info' class="ui-body alert-info"></div>
@endif

@if(empty($rats))
<p>TODO: Instructions.</p>
@endif

<div id="addCronrat" data-role="collapsible-set" data-theme="a" data-content-theme="a">

	@if($errors->has('error'))
	<div class="alert alert-danger">{{$errors->first('err')}}</div>
	@endif
	<div data-role="collapsible">
		<h2>Add New Cronrat</h2>
		<ul data-role="listview" data-theme="a" data-divider-theme="a">
			<li>
				<form action="{{ URL::to('cronrat/add') }}" method="post" data-ajax="false">
					<fieldset>
					    <input name="cronratName" id="cronratName" value="" type="text" class="form-control" placeholder="Cron Job Name">

					    @if( Request::old('failEmail') != '' )
						<input name="failEmail" id="failEmail" value="{{ Request::old('failEmail') }}" type="email" data-clear-btn="true" placeholder="Notify Email Address">
                        @else
                        <input name="failEmail" id="failEmail" value="{{ Sentry::getUser()->email }}" type="email" data-clear-btn="true" placeholder="Notify Email Address">
                        @endif

                        <input name="failUrl" id="failUrl" value="" type="hidden" oldtype='url' data-clear-btn="true" placeholder="Notify Pull Url" data-clear-btn="true">

                        <select name="ttl" id="ttl" data-native-menu="false">
                            <option value="86400" selected="1">Check : Every Day</option>
        					<option value="43200">Every 12 hours</option>
        					<option value="21600">Every 6 hours</option>
        					<option value="3600">Every hour</option>
        				</select>
                        <input id="cid" val="" type="hidden">
						<input type="submit" id="submitRat"  name="submitRat" data-theme="a" value="Submit"> </br>
					</fieldset>
				</form>
			</li>
		</ul>
	</div>

		@if(!empty($rats))
	<div data-role="collapsible" data-collapsed="false">
		<h2>My Cron Rats</h2>
    <ul id="ratlist" data-role="listview">

		@foreach ($rats as $rat)
 	        <li><a href="#popupRat" data-rel="popup" data-transition="slideup" class='ratId' id='{{ $rat->cronrat_code }}'><img  id='{{ $rat->cronrat_code }}_img' src="/assets/images/{{ $rat->active }}.png" alt="ok" class="ui-li-icon ui-corner-none">{{ $rat->cronrat_name }}</a></li>
		@endforeach
		</ul>
	</div>
	@endif
</div>

<div data-role="popup" id="popupRat" class="popupRat" data-theme="b">
<a href="#" data-rel="back" data-role="button" data-theme="b" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
        </br>
        <ul data-role="listview" data-inset="true" style="min-width:210px;">
            <li><a href="#" class="ratToggle">Toggle On/Off</a></li>
            <li><a href="#" class="ratUrl">Show Rat Url</a></li>
            <li><a href="#" class="ratDelete">Delete</a></li>
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
