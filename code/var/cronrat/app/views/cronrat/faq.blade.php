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

    To start monitoring your job simply call url (like in example below).
    This will start counter, if url is not refreshed within preconfigured time,
    you will get alerted.

     such as this:

    http(s)://cronrat.com/r/CRONRATKEY/JOBNAME ? [NEXTCHECK= minutes]&[EMAILTO=string]&[URLTO=string]&[ACTIVEON=string]
    <b>CRONRATKEY</b> (required) - cronrat key you receive for your account
    <b>JOBNAME</b> (required, max 256 char) - Unique (for your account) Alert name, alphanumeric URL encoded please
    <b>NEXTCHECK</b> (optional) - a time MINUTES to wait for next check before alert (default 1440 = 24 hours).
    <b>EMAILTO </b>(optional) - by default alert will be sent to registered email (paid accountscan overwrite it here)
    <b>URLTO</b>(optional) -  (paid accounts only) the url to pull http or https upon alert
    <b>ACTIVEON</b> (optional) - a flag that allows "skipping" of a check on some weekdays.
         defined as string of 7 day characters starting from Monday through Sunday "MTWTFSS" or (1111111)  where, if day character at position replaced with 0, the check will not run on that day.
         Example MTWTF00 or 1111100 will only run on weekdays and skip saturday and sunday.
         this is good for obs that do not run on say weekeds. Default is to run everyday.
    <b>TOUTC</b> (optional) - Offset in seconds between your job's time zone and UTC, example for PST offset is 25200sec

    <b>IMPORTANT</b> please note all curl urls are enclosed in "quotes" to preserve special characters.

    example urls:
    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"
    <i>will alert if job BackupMySQL not run in next 24 hours </i>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww?NEXTCHECK=30&EMAILTO=4155551212%40txt.att.net"
    <i>will alert if job BackupMySQL not run in next 30 minutes and will send email (sms) to 4155551212@txt.att.net (Note Url encoded @ is %40)</i>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww?NEXTCHECK=30&EMAILTO=4155551212%40txt.att.net&URLTO=http%3A%2F%2Fmyserver.com%2Frebootsql.php"
    <i>will alert if job BackupMySQL not run in next 30 minutes and will send email (sms) to 4155551212@txt.att.net
    and pull url: http://myserver.com/rebootsql.php (Note Url is encoded)</i>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww?NEXTCHECK=30&EMAILTO=4155551212%40txt.att.net&URLTO=http%3A%2F%2Fmyserver.com%2Frebootsql.php&ACTIVEON=MTWTF00&TOUTC=25200"
    <i>Active only on weekdays and will alert if job BackupMySQL not run in next 30 minutes and will send email (sms) to 4155551212@txt.att.net
    and pull url: http://myserver.com/rebootsql.php (Note Url is encoded)</i>


3. Integration Into Various scripts

From Crontab

0 0 * * * /usr/bin/backintime  --backup-job  && curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"

Or you can used from inside of your scripts, just call Cronrat url

//Bash Example
curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"

PHP example
// create a new cURL resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);

//RUBY
require "net/http"
...
Net::HTTP.get("cronrat.com", "/r/{{$cronrat_code}}/BackupMysqlWww")

//Python
import webbrowser
...
webbrowser.open("http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww")

//Powershell
powershell -ExecutionPolicy unrestricted -Command "(New-Object Net.WebClient).DownloadString(\"http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww\")"

//golang
import "net/http"
..
res, err := http.Get("http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww")

you got the idea right??

5. You can see all of your Cronrats using nice UI

6. Free accounts get 10 cronrats, need more?? it is only $29 a year

7. Cronrat will notify of failure 3 times., thereafter it will go dormant (and you will get 1 more cronrat available to you) and be deleted in 30 days. nothing to do for you.
</pre>

<h2>How do i get my Cronrat URl</h2>
<p>Signup for service and you can have unlimited (almost) number of Cronrat URLs for all of your jobs.</p>

<h2>Who uses Cronrat?</h2>
<p>Sorry you had to ask, because we will not tell you, nor we sell their emails.</p>

<h2>What technology did you use to build Cronrat?</h2>
<p>Golang, REDIS, Laravel, MYSQL, PHP, JQuery Mobile and Phonegap</p>

<h2>What time is it on your server</h2>
<?php
date_default_timezone_set('UTC');
echo date('Y-m-d h:i:s T',time());
?>

@stop
