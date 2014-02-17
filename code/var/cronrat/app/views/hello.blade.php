@extends('layouts.jquerymobile')

{{-- Web site Title --}}
@section('title')
@parent
Cronrat
@stop

{{-- Content --}}
@section('content')

			<div data-role="collapsible-set" data-theme="b" data-content-theme="d">
				<div data-role="collapsible">
					<h2>Executive Summary</h2>
					<ul data-role="listview" data-filter="true" data-filter-theme="c" data-divider-theme="d">
						<li><a href="index.html">Adam Kinkaid</a></li>
						<li><a href="index.html">Alex Wickerham</a></li>
						<li><a href="index.html">Avery Johnson</a></li>
						<li><a href="index.html">Bob Cabot</a></li>
						<li><a href="index.html">Caleb Booth</a></li>
					</ul>
				</div>
				<div data-role="collapsible">
					<h2>Sales</h2>
					<ul data-role="listview" data-theme="d" data-divider-theme="d">
						<li data-role="list-divider">Friday, October 8, 2010 <span class="ui-li-count">2</span></li>
						<li><a href="index.html">
							<h3>Stephen Weber</h3>
							<p><strong>You've been invited to a meeting at Filament Group in Boston, MA</strong></p>
							<p>Hey Stephen, if you're available at 10am tomorrow, we've got a meeting with the jQuery team.</p>
							<p class="ui-li-aside"><strong>6:24</strong>PM</p>
						</a></li>
						<li><a href="index.html">
							<h3>jQuery Team</h3>
							<p><strong>Boston Conference Planning</strong></p>
							<p>In preparation for the upcoming conference in Boston, we need to start gathering a list of sponsors and speakers.</p>
							<p class="ui-li-aside"><strong>9:18</strong>AM</p>
						</a></li>
					</ul>
				</div>
				<div data-role="collapsible">
					<h2>Funnel</h2>
					<ul data-role="listview" data-split-icon="gear" data-split-theme="d">
						<li><a href="index.html">
							<img src="images/album-bb.jpg" />
							<h3>Broken Bells</h3>
							<p>Broken Bells</p>
							</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
						<li><a href="index.html">
							<img src="images/album-hc.jpg" />
							<h3>Warning</h3>
							<p>Hot Chip</p>
						</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
						<li><a href="index.html">
							<img src="images/album-p.jpg" />
							<h3>Wolfgang Amadeus Phoenix</h3>
							<p>Phoenix</p>
							</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
					</ul>
				</div>
				<div data-role="collapsible">
					<h2>Finance</h2>
					<ul data-role="listview" data-split-icon="gear" data-split-theme="d">
						<li><a href="index.html">
							<img src="images/album-bb.jpg" />
							<h3>Broken Bells</h3>
							<p>Broken Bells</p>
							</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
						<li><a href="index.html">
							<img src="images/album-hc.jpg" />
							<h3>Warning</h3>
							<p>Hot Chip</p>
						</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
						<li><a href="index.html">
							<img src="images/album-p.jpg" />
							<h3>Wolfgang Amadeus Phoenix</h3>
							<p>Phoenix</p>
							</a><a href="lists-split-purchase.html" data-rel="dialog" data-transition="slideup">Purchase album
						</a></li>
					</ul>
				</div>
			</div>
@stop