<!-- Navbar -->
       <div class="navbar navbar-inverse navbar-fixed-top">
           <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="{{ URL::to('') }}">Help Base</a>
            </div>

            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    @if (Sentry::check() && Sentry::getUser()->hasAccess('admin'))
                    <li {{ (Request::is('users*') ? 'class="active"' : '') }}><a href="{{ URL::to('/users') }}">Users</a></li>
                    <li {{ (Request::is('groups*') ? 'class="active"' : '') }}><a href="{{ URL::to('/groups') }}">Groups</a></li>
                    <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                          <ul class="dropdown-menu">
                                <li><a href="#">Action</a></li>
                                <li><a href="#">Another action</a></li>
                                <li><a href="#">Something else here</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Nav header</li>
                                <li><a href="#">Separated link</a></li>
                                <li><a href="#">One more separated link</a></li>
                          </ul>
                    </li>
                    @endif
                </ul>
                @if (Sentry::check())

					<ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ Sentry::getUser()->email }}<b class="caret"></b></a>
                          <ul class="dropdown-menu">
                                <li><a href="{{ URL::to('/users/edit/'.Sentry::getUser()->id) }}">Profile</a></li>
                                 @if (Sentry::check() && Sentry::getUser()->hasAccess('admin'))
                                    <li {{ (Request::is('users*') ? 'class="active"' : '') }}><a href="{{ URL::to('/users') }}">Users</a></li>
                                    <li {{ (Request::is('groups*') ? 'class="active"' : '') }}><a href="{{ URL::to('/groups') }}">Groups</a></li>
                                 @endif
                                <li><a href="{{ URL::to('users/logout') }}">Logout</a></li>
                          </ul>
                    </li>
                    </ul>


                @else
                    <form class="navbar-form navbar-right" action="{{ URL::to('users/login') }}" method="post">
                    {{ Form::token(); }}
                        <div class="form-group">
                            <input name="email" id="email" value="{{ Request::old('email') }}" type="text" class="form-control" placeholder="E-mail">
                        </div>
                        <div class="form-group">
                          <input name='password' type="password" placeholder="Password" class="form-control">
                        </div>
                         <input class="btn btn-primary input-group-sm" type="submit" value="Log In"> </br>
                           <label class="checkbox inline">
                               <a class="btn btn-link">
                               <input type="checkbox" name="rememberMe" value="1"> Remember Me
                               </a>
                                <a class="btn btn-link"> | </a>
                               <a href="{{ URL::to('users/resetpassword') }}" class="btn btn-link">Forgot Password?</a>
                               <a class="btn btn-link"> | </a>
                               <a href="{{ URL::to('users/register') }}" class="btn btn-link">Register</a>
                           </label>
                   </form>
                @endif

				</div>
			</div>
		</div>
		<!-- ./ navbar -->