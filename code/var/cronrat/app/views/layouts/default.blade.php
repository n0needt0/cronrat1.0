<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" href="favicon.ico">
	<title>
		    @section('title')
		    @show
	</title>
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
<link rel="stylesheet" href="/assets/vendor/jquery.mobile/css/jquery.mobile-1.3.0.min.css" />

<!-- jQuery Mobile CSS bits -->
<link rel="stylesheet" href="/assets/css/custom.css" />

<!-- js libs-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="/assets/vendor/jquery.mobile/jquery.mobile-1.3.0.min.js"></script>
<script src="/assets/vendor/phonegap/ios-orientationchange-fix.min.js"></script>
<script src="/assets/vendor/phonegap/cordova-2.5.0.js"></script>

<!-- Startup Images for iDevices -->
<script>(function(){var a;if(navigator.platform==="iPad"){a=window.orientation!==90||window.orientation===-90?"images/startup-tablet-landscape.png":"images/startup-tablet-portrait.png"}else{a=window.devicePixelRatio===2?"images/startup-retina.png":"images/startup.png"}document.write('<link rel="apple-touch-startup-image" href="'+a+'"/>')})()</script>
<!-- The script prevents links from opening in mobile safari. https://gist.github.com/1042026 -->
<script>(function(a,b,c){if(c in b&&b[c]){var d,e=a.location,f=/^(a|html)$/i;a.addEventListener("click",function(a){d=a.target;while(!f.test(d.nodeName))d=d.parentNode;"href"in d&&(d.href.indexOf("http")||~d.href.indexOf(e.host))&&(a.preventDefault(),e.href=d.href)},!1)}})(document,window.navigator,"standalone")</script>
<!-- List of JS libs we use -->

<script>
        $(document).ready( function () {
            $.mobile.ajaxEnabled = false;
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

        var jsLibs = {
        	        // Libraries
        	        jquery: "{{Config::get('app.url')}}/assets/vendor/jquery/jquery-1.8.2-min",
        	        jquerymobile: "{{Config::get('app.url')}}/assets/vendor/jquery.mobile/jquery.mobile-1.3.0.min",
        	        iosorientation: "{{Config::get('app.url')}}/assets/vendor/phonegap/ios-orientationchange-fix.min",
        	        cordova: "{{Config::get('app.url')}}/assets/vendor/phpnegap/cordova-2.5.0",
        	        underscore: "{{Config::get('app.url')}}/assets/vendor/underscore",
        	        backbone: "{{Config::get('app.url')}}/assets/vendor/backbone",
        	        json2: "{{Config::get('app.url')}}/assets/vendor/json2",
        	        // Shim Plugin
        	        use: "{{Config::get('app.url')}}/assets/vendor/require/plugins/use",
        	        async: "{{Config::get('app.url')}}/assets/vendor/require/plugins/async"
        	      };

    </script>
</head>
<body>

<script>
var head= document.getElementsByTagName('head')[0];
var script= document.createElement('script');
script.setAttribute('type', 'text/javascript');
script.setAttribute('src', '/assets/vendor/require/require.js');
script.setAttribute('data-main', "/assets/js/home.config");
head.appendChild(script);
</script>

<div data-role="page">
	<div data-role="header" data-theme="{{Config::get('app.jqm_theme')}}">
		<h1>{{Config::get('app.app_name')}}</h1>
		<a href="{{ URL::to('') }}" data-icon="home" data-iconpos="notext" data-direction="reverse">Home</a>
		@if (Sentry::check())
    		<a href="#popupAcc" data-rel="popup" data-role="button" data-icon="gear">{{ Sentry::getUser()->email }}</a>
			<div data-role="popup" id="popupAcc">
				<ul data-role="listview" data-inset="true" style="min-width:210px;" data-theme="{{Config::get('app.jqm_theme')}}">
					<li data-role="divider" >Options</li>
					@if (Sentry::check() && Sentry::getUser()->hasAccess('admin'))
							<li {{ (Request::is('users*') ? 'class="active"' : '') }}><a href="{{ URL::to('/users') }}">Users</a></li>
							<li {{ (Request::is('groups*') ? 'class="active"' : '') }}><a href="{{ URL::to('/groups') }}">Groups</a></li>
				    @endif
					<li><a href="{{ URL::to('/users/edit/'.Sentry::getUser()->id) }}">Account</a></li>
					<li><a href="{{ URL::to('users/logout') }}">Logout</a></li>
				</ul>
		    </div>
		@endif
	</div><!-- /header -->

	<div data-role="content">
            <!-- Notifications -->
              @include('notifications')
            <!-- ./ notifications -->
            <!--  content -->
              @yield('content')
            <!--  ./content -->
	</div>

	<div data-role="footer" data-position="fixed" data-theme="{{Config::get('app.jqm_theme')}}">
	    <h4> Copyright &copy; {{ date('Y'); }} helppain.net All Rights Reserved | <?php echo View::make('partials.version') ?></h4>
    </div>
</div><!-- /page -->
</body>
</html>
