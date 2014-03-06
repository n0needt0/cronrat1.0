<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico">
<title>Cronrat.com Cron Monitoring and Solution to Cron Spam</title>
<meta HTTP-EQUIV="Pragma" CONTENT="no-cache">
<meta HTTP-EQUIV="Expires" CONTENT="-1">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320">

<!-- Home screen icon  Mathias Bynens mathiasbynens.be/notes/touch-icons -->
<!-- For iPhone 4 with high-resolution Retina display: -->
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="apple-touch-icon.png">
<!-- For first-generation iPad: -->
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="apple-touch-icon.png">
<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
<link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">
<!-- For nokia devices and desktop browsers : -->
<link rel="shortcut icon" href="favicon.ico" />

<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
<meta http-equiv="cleartype" content="on">

<!-- jQuery Mobile CSS bits -->
<link rel="stylesheet" href="//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css" />

<!-- jQuery Mobile CSS bits -->
<link rel="stylesheet" href="/assets/css/custom.css" />

<!-- js libs-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>

<!-- Startup Images for iDevices -->
<script>(function(){var a;if(navigator.platform==="iPad"){a=window.orientation!==90||window.orientation===-90?"images/startup-tablet-landscape.png":"images/startup-tablet-portrait.png"}else{a=window.devicePixelRatio===2?"images/startup-retina.png":"images/startup.png"}document.write('<link rel="apple-touch-startup-image" href="'+a+'"/>')})()</script>
<!-- The script prevents links from opening in mobile safari. https://gist.github.com/1042026 -->
<script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(d.href.indexOf("http")||~d.href.indexOf(e.host))&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")</script>
<!-- List of JS libs we use -->

<script>
        $.mobile.ajaxEnabled = false;

        $.ajaxSetup ({
         // Disable caching of AJAX responses
         cache: false
         });

        var Conf = Conf || {};

        Conf.server_name = '<?php echo $_SERVER['SERVER_NAME']?>';
        Conf.protocol = 'http';
        <?php
        if(!empty($_SERVER['SERVER_HTTPS']))
        {
        	echo "Conf.protocol = 'https';";
        }
        ?>

        Conf.home = "{{Config::get('app.url')}}";

        function debug(msg){

           if('debugger' === "{{Config::get('app.jsdebug')}}")
           {
               eval('debugger;');
           }

           if('console' === "{{Config::get('app.jsdebug')}}")
           {
               console.log(msg);
           }
        }
    </script>

<!-- runtimejs -->
    @yield('runtimejs')
<!-- runtimejs -->

</head>
<body>

<div data-role="page" id="@yield('pageid', isset($pageid) ? $pageid : Request::path())" data-cache="false">
<div data-role="header" data-theme="{{Config::get('app.jqm_theme')}}">
    <div data-role="navbar">
    <ul>
    <li>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="2CGX9VTK3KQ8U">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
	</li>
		@if (!Sentry::check())
		<li><a href="{{ URL::to('/in') }}">Login</a></li>
		@else
		<li><a href="{{ URL::to('/') }}"> {{Config::get('app.app_name')}} </a></li>
		<li><a href="{{ URL::to('/help') }}">FAQ</a></li>
		<li><a href="{{ URL::to('/users/edit/'.Sentry::getUser()->id) }}">Profile</a></li>
		<li><a href="{{ URL::to('users/logout') }}">Logout</a></li>
		@endif
	</ul>
	</div>
</div>
<!-- /header -->

	<div data-role="content">
            <!-- Notifications -->
              @include('notifications')
            <!-- ./ notifications -->
            <!--  content -->
              @yield('content')
            <!--  ./content -->
	</div>

	<div data-role="footer" data-position="fixed" data-theme="{{Config::get('app.jqm_theme')}}">
	    <h3> &copy; {{ date('Y'); }} {{Config::get('app.copyright')}} <?php echo View::make('partials.version') ?></h3>
    </div>
</div><!-- /page -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48152822-1', 'cronrat.com');
  ga('send', 'pageview');

</script>
</body>
</html>
