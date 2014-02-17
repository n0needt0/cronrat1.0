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
<h2>Hody Pilgrim. Did your cron backup run last night??</h2>

<h2>Cron biggest problem</h2>
<p>All I want is to know when Cron job fails!!! You keep sending me email that it is ok :)</p>

<h2>What is Cronrat?!</h2>
<p>Cronrat is a scheduler (i.e. Cron) monitoring tool. It will notify you when your job fails. Without cram spam. Built by Andrew Yasinsky and two hamsters in California, USA.
</p>

<h2>How does it work</h2>
<pre>
Super Crazy Simple, Same on Unix or Windowz

1. You call job's Cronrat url (http or https) everytime your job (Cron) runs successfully.
2. We notify you if your job did not call its Cronrat url within selected time frame
3. You Run, Scream and Shout till it all fixed up..

From Crontab

0 0 * * * /usr/bin/backintime  --backup-job  && curl http://cronrat.com/r/YOURCRONRATCODE &> /dev/null

Or you can used from inside of your scripts, just call Cronrat url

//Bash Example
curl -l http://cronrat.com/r/YOURCRONRATCOD &> /dev/null

PHP example
// create a new cURL resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://cronrat.com/r/YOURCRONRATCODE");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);

//RUBY
require "net/http"
...
Net::HTTP.get("cronrat.com", "/r/YOURCRONRATCODE")

//Python
import webbrowser
...
webbrowser.open("http://cronrat.com/r/YOURCRONRATCODE")

//Powershell
powershell -ExecutionPolicy unrestricted -Command "(New-Object Net.WebClient).DownloadString(\"http://cronrat.com/r/YOURCRONRATCODE\")"

//golang
import "net/http"
..
res, err := http.Get("http://cronrat.com/r/YOURCRONRATCODE")

you got the idea right??
</pre>

<h2>How do i get my Cronrat URl</h2>
<p>Signup for service and you can have unlimited (almost) number of Cronrat URLs for all of your jobs.</p>

<h2>Why Is it free</h2>
<p>I thought it be nice after years of using open source to donate some of my time to good cause.</p>

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
