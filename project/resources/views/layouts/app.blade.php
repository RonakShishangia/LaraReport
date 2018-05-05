<html lang="en">
<head>
	<title>{{"Nkonnect infoway"}}</title>
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/toastr.css')}}" >

	@yield('styles')
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
			<a class="navbar-brand" href="{{url('/')}}">Import - CSV </a>
			</div>
			<ul class="nav navbar-nav">

				@if (Auth::guest())
					{{-- <li><a href="{{ route('login') }}">Login</a></li> --}}
				@else
					<li class=""><a href="{{route('searchemp')}}">Report</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Masters
						<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="{{route('company.index')}}">Company</a></li>
							<li><a href="{{ route('department.index') }}">Department</a></li>
							<li><a href="{{ route('employee.index') }}">Employees</a></li>
						</ul>
					</li>

					{{-- <li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							{{ Auth::user()->name }}
						</a>

						<ul class="dropdown-menu" role="menu">
							<li>
								<a href="{{ route('logout') }}"
									onclick="event.preventDefault();
											document.getElementById('logout-form').submit();">
									Logout
								</a>

								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
									{{ csrf_field() }}
								</form>
							</li>
						</ul>
					</li> --}}
				@endif
			</ul>
			@if (Auth::check())
				<ul class="nav navbar-nav navbar-right">
						<li>
							<a href="{{ route('logout') }}"
								onclick="event.preventDefault();
										document.getElementById('logout-form').submit();">
								Logout
							</a>
							<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
								{{ csrf_field() }}
							</form>
						</li>
						<li><a href="#"><span class="glyphicon glyphicon-user"></span> {{ Auth::user()->name }}</a></li>
				</ul>
			@endif
		</div>
	</nav>
	<div class="container-fluid">
	    @yield('content')
	</div>


	<!-- Modal -->
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<form action="{{route('addnote')}}" method="post">
				{{ csrf_field() }}
			<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title" id="userData">Modal Header</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="comment">NOTE:</label>
							<input type="hidden" name="userId" id="userId">
							<textarea class="form-control" rows="5" id="adduserNote" name="adduserNote"></textarea>
						</div>
					</div>
						<div class="modal-footer">
							<button type="submit" name="button" class="btn btn-primary">Save</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<script src="{{ asset('js/app.js') }}"></script>
	<script src="{{ asset('js/toastr.min.js') }}"></script>
	<script>
		/**
		 * Toster notification Config
		 */
		toastr.options = {
			"closeButton": false,
			"newestOnTop": false,
			"positionClass": "toast-top-right",
			"timeOut": "8000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "fadeIn",
			"hideMethod": "fadeOut"
		}
		/**
		 * Toster notification settings
		 */
		@if(Session::has('success'))
			toastr.success("{{ Session::get('success') }}");
		@endif
		@if(Session::has('info'))
			oastr.info("{{ Session::get('info') }}");
		@endif
		@if(Session::has('warning'))
			toastr.warning("{{ Session::get('warning') }}");
		@endif
		@if(Session::has('error'))
			toastr.error("{{ Session::get('error') }}");
		@endif
	</script>
    @yield('script')
</body>
</html>
