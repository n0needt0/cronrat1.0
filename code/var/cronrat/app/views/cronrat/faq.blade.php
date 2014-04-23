@extends('layouts.jquerymobile') {{-- Web site Title --}}
@section('title') Cronrat @stop {{-- Content --}}
@section('runtimejs')

<script>
$.mobile.ajaxEnabled = false;

$.ajaxSetup ({
 // Disable caching of AJAX responses
 cache: false
 });

$(document).on("pagecreate", function (e) {
});

</script>
@stop
@section('content')

<h2>Cron biggest problem</h2>
<p>All i [you, him, us, everyone..] want to know is when scheduled job fails, instead we get message when jobs succeeds :(</p>
<h2>Well this is about to change!</h2>

<h2>What is Cronrat?!</h2>
<p>Cronrat is a monitoring tool. It will alert when job fails. Without cron spam (Cram). Powered by two golang hamsters, yes yes in clouds.
</p>


<h2>How does it work</h2>

<pre>
Super Awesome Nice &copy; 2014 cronrat.com

1. Register your account
2. Get Cronrat key.
3. Thats it...
    Now you can deploy manually or via script.
    No need to use UI and nothing to configure.

    To start monitoring your job simply call url via get or post (like in example below).
    This will start counter and if url is not pulled next time as defined in CRONTAB you will get alerted.

<h2>API Definition</h2>

    [POST or GET] http(s)://cronrat.com/r/CRONRATKEY?RAT=Your Rat Name [..optional parameters]

    All Parameters needs to be URL encoded !

    <b>CRONRATKEY</b> (required) - cronrat key you receive for your account
    <b>RAT</b> (required, max 256 char) - Something to identifies this cronrat for you
    <b>CRONTAB</b> (optional) - unix CRONTAB command without script portion (default 0 0 * * * every day at midnight)
    <b>ALLOW</b> (optional) - seconds to allow before issue alert. minimum 300 (5min) maximum and default is 86400 (24 hours) so by default job needs to run at least once every 24hr.
    <b>EMAILTO </b>(optional) - by default alert will be sent to registered email, this parameter allows for different emal address
    <b>URLTO</b>(optional) -  url to pull http or https upon alert
    <b>TOUTC</b> (optional) - Offset in hours between you and UTC, example for America/Los_Angeles offset is -7

    <b>Example URLs with standard linux curl:</b>

        -for GET request (specify -G flag , default goes by POST)
        -note all parameters wrapped separately in --data-urlencode

    curl -G "http://cronrat.com/r/{{$cronrat_code}}" --data-urlencode "RAT=Backup Mysql Www" --data-urlencode "CRONTAB=0 * * * *" --data-urlencode "EMAILTO=myalets@myemail.com"

<h2>Usage With Crontab</h2>

0 0 * * * /usr/bin/backintime  --backup-job  && curl -G "http://cronrat.com/r/{{$cronrat_code}}" --data-urlencode "RAT=Backup Mysql Www" --data-urlencode "CRONTAB=0 * * * *" --data-urlencode "EMAILTO=myalets@myemail.com"

Or you can used from inside of your scripts as a standard url call.

You can see all of your Cronrats using nice UI

Cronrat will notify of failure 3 times., thereafter it will go dormant (and you will get 1 more cronrat available to you) and be deleted in 30 days. nothing to do for you.
</pre>

<h2>How do i get my Cronrat URl</h2>
<p>Signup for service and you can have unlimited (almost) number of Cronrat URLs for all of your jobs.</p>

<h2>What technology did you use to build Cronrat?</h2>
<p>Golang, REDIS, Laravel, MYSQL, PHP, JQuery Mobile and Phonegap</p>

<h2>What time is it on your server</h2>
<?php
date_default_timezone_set('UTC');
echo date('Y-m-d h:i:s T',time());
?>

@stop
