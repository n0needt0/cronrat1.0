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

    [POST or GET] http(s)://cronrat.com/r/CRONRATKEY/JOBNAME ? ..optional parameters

    <b>CRONRATKEY</b> (required) - cronrat key you receive for your account
    <b>JOBNAME</b> (required, max 256 char) - Unique (for your account) Alert name, alphanumeric URL encoded please
    <b>CRONTAB</b> (optional) - a url encoded unix CRONTAB command without script portion (default 0 0 * * * every day at midnight) or when url encoded (0+0+%2A+%2A+%2A)
    <b>ALLOW</b> (optional) - seconds to allow before issue alert. minimum 300 (5min) maximum and default is 86400 (24 hours) so by default job needs to run at least once every 24hr.
    <b>EMAILTO </b>(optional) - by default alert will be sent to registered email, this parameter allows for an alternative emal
    <b>URLTO</b>(optional) -  (paid accounts only) the url to pull http or https upon alert
    <b>TOUTC</b> (optional) - Offset in hours between you and UTC, example for America/Los_Angeles offset is -7

    <b>IMPORTANT</b> please note all curl urls are enclosed in "quotes" to preserve special characters.

    <b>example urls:</b>b>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"
    <i>will alert if job BackupMySQL does not run in next 25 hours (24hours plus 1hour to allow job finish) </i>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww?CRONTAB={{urlencode('15 0 * * *')}}&EMAILTO={{ urlencode('4155551212@txt.att.net')}}"
    <i>will alert if job BackupMySQL does not run 1 hour after 15 minutes past midnight (15 0 * * *) and will send email (sms) to 4155551212@txt.att.net (Note Url encoded @ is %40)</i>

    curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww?CRONTAB={{urlencode('15 0 * * *')}}&EMAILTO={{ urlencode('4155551212@txt.att.net')}}"&URLTO=http%3A%2F%2Fmyserver.com%2Frebootsql.php"
    <i>same as above also will pull url: http://myserver.com/rebootsql.php (Note Url is encoded)</i>

<h2>Integration Into Various scripts</h2>

From Crontab

0 0 * * * /usr/bin/backintime  --backup-job  && curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"

Or you can used from inside of your scripts, just call Cronrat url

//Bash Example
curl "http://cronrat.com/r/{{$cronrat_code}}/BackupMysqlWww"

//PHP example
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

7. Cronrat will notify of failure 3 times., thereafter it will go dormant (and you will get 1 more cronrat available to you) and be deleted in 30 days. nothing to do for you.
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
