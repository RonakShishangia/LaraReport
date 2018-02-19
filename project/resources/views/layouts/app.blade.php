<html lang="en">
<head>
	<title>{{"Nkonnect infoway"}}</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" >
	@yield('styles')
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<a class="navbar-brand" href="#">Import - CSV </a>
			</div>
		</div>
	</nav>
	<div class="container-fluid">
	    @yield('content')
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('script')
</body>
</html>