<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Meta Information -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>@yield('title') {{ config('app.name') }}</title>

	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800|Poppins:400,500,600,700,900|Roboto:400,500,700,900" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">

	<!-- CSS -->
	<link href="{{ mix(Spark::usesRightToLeftTheme() ? 'css/app-rtl.css' : 'css/app.css') }}" rel="stylesheet">

	<!-- Scripts -->
	@yield('scripts', '')

	<!-- Global Spark Object -->
	<script>
		<?php
		/**
		 * [$spark_autofill description]
		 * @var array
		 */
		$spark_autofill = [
			'autofill' => [],
		];
		if ( isset( $_GET['email'] ) ) {
			$spark_autofill['autofill']['email'] = $_GET['email'];
		}
		?>
		@if(isset($user_default_store))
			window.Spark =
			<?php
			echo json_encode(
				array_merge(
					Spark::scriptVariables(),
					$customers_page_sess,
					[
						'default_store' => $user_default_store,
						'timezone'      => $timezone,
						'settings'      => $settings,
					]
				)
			);
			?>
			;
		@else
			window.Spark =
			<?php
			echo json_encode(
				array_merge(
					Spark::scriptVariables(),
					$customers_page_sess,
					$spark_autofill
				)
			);
			?>
			;
		@endif
	</script>
</head>
<body>
	<div id="spark-app" v-cloak>
		<!-- Navigation -->
		@if (Auth::check())
			@include('spark::nav.user')
		@else
			@include('spark::nav.guest')
		@endif

		<!-- Main Content -->
		<main>
			@yield('content')
		</main>

		<!-- Application Level Modals -->
		@if (Auth::check())
			@include('spark::modals.support')
			@include('spark::modals.session-expired')
		@endif
	</div>

	<!-- JavaScript -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCienqwdIrTy5BGUnpppMrrWPR59fJqJ7k"></script>
	<script src="https://unpkg.com/v-tooltip"></script>
	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
	<script src="https://cdn.jsdelivr.net/npm/vue-apexcharts"></script>
	<script src="{{ mix('js/app.js') }}"></script>
	<script src="/js/sweetalert.min.js"></script>
	<script>
	  window.intercomSettings = {
		app_id: "gquhkg71"
	  };
	</script>
	<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/gquhkg71';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
	@yield('javascript')
</body>
</html>
