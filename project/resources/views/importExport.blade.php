@extends('layouts.app')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/datatables.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/responsive.bootstrap.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/fixedHeader.bootstrap.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/toastr.css')}}" >	
@endsection

@section('content')
	<div class="row text-center">
    	<form action="{{ URL::to('importExcel') }}" class="form-inline" method="post" enctype="multipart/form-data">
			<div class="col-md-3">
				{{ csrf_field() }}
				<!-- <a href="{{ URL::to('downloadExcel/xls') }}"><button class="btn btn-success">Download Excel xls</button></a>
				<a href="{{ URL::to('downloadExcel/xlsx') }}"><button class="btn btn-success">Download Excel xlsx</button></a>
				<a href="{{ URL::to('downloadExcel/csv') }}"><button class="btn btn-success">Download CSV</button></a> -->
				<input type="file" class="btn btn-primary" name="import_file" id="import_file"  required/>
			</div>
			<div class="col-md-9 text-left">
				<button class="btn btn-primary hidden" id="csv-upload" >Import File</button>
			</div>
		</form>
	</div>
	<div class="row text-center" style="padding-top: 35px;">
		<div class="col-md-1 text-right">
			<label for="">Date Filter : </label>
		</div>
		<div class="col-md-3">
			<div class="input-group input-daterange">
				<input type="text" id="min-date" class="form-control date-range-filter" data-date-format="yyyy-mm-dd" placeholder="From:">
				<div class="input-group-addon">to</div>
				<input type="text" id="max-date" class="form-control date-range-filter" data-date-format="yyyy-mm-dd" placeholder="To:">
			</div>
		</div>
		<div class="col-md-1">
			<button type="button" class="btn btn-primary" id="resetDateFilter">Reset</button>
		</div>
	</div>
	<hr>	
	<div class="table-responsive">
		<table id="example" class="table table-striped table-bordered small" width="100%" cellspacing="0">
			<thead>
				<tr>
					<th>Date</th>
					<th>Name</th>
					<th>Department</th>
					<th>Office In</th>
					<th>Office Out</th>
					<th>Attendance</th>
					<th>Total Time</th>
					<th>Worked Time</th>
					<th>Total Break Time</th>
					<th>Thumb</th>
					<th>Note</th>
					<th>Breaks</th>
					{{--<th>LE</th>
					<th>OT</th>--}}
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Date</th>
					<th>Name</th>
					<th>Department</th>
					<th>Office In</th>
					<th>Office Out</th>
					<th>Attendance</th>
					<th>Total Time</th>
					<th>Worked Time</th>
					<th>Total Break Time</th>
					<th>Thumb</th>
					<th>Note</th>
					<th>Breaks</th>
					{{--<th>LE</th>
					<th>OT</th>--}}
				</tr>
			</tfoot>
		</table>
	</div>

@endsection

@section('script')		
	<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('js/dataTables.bootstrap.min.js') }}"></script>
	<script src="{{ asset('js/dataTables.fixedHeader.min.js') }}"></script>
	<script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('js/responsive.bootstrap.min.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}" charset="utf-8"></script>
	<script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('js/bootstrap-datepicker.min.js') }}" charset="utf-8"></script>
	<script src="{{ asset('js/buttons.print.min.js') }}"></script>
	<script src="{{ asset('js/buttons.html5.min.js') }}"></script>
	<script src="{{ asset('js/pdfmake.min.js') }}"></script>
	<script src="{{ asset('js/vfs_fonts.js') }}"></script>
	<script src="{{ asset('js/jszip.min.js') }}"></script>
	<script src="{{ asset('js/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('js/toastr.min.js') }}"></script>
	<script>
		var table='';
		$(document).ready(function() {
			table=$('#example').DataTable({
				data: <?php echo $data; ?> ,
				pageLength: 25,
				dom: 'Bfrtip',
				buttons: [
					'pageLength', 'excel'//, 'csv', 'excel', 'pdf', 'print'
				],
				fixedHeader: true,
				"columns":[
					{ data: "date", name: 'date' },
					{ data: "name", name: "name" },
					{ data: "department", name: "department" },
					{ data: "officeIn", name: "officeIn", 
						render:function (data, type, row) {
							if(data == "00:00:00"){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "officeOut", name: "officeOut", 
						render:function (data, type, row) {
							if(data == "00:00:00"){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "attendance" , name: 'attendance',
						render:function (data, type, row) {
							if(data == "Absent"){
								return "<center><span style='color:red;'>"+ data +"</span></center>";
							}else{
								return "<center><span style='color:green;'>"+ data +"</span></center>"; 
							}
						} 
					},
					{ data: "total_time", name: "total_time", 
						render:function (data, type, row) {
							if(data == "00:00:00"){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "worked_time", name: "worked_time", 
						render:function (data, type, row) {
							if(data == "00:00:00"){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "total_break_time", name: "total_break_time", 
						render:function (data, type, row) {
							if(data == "00:00:00"){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "thumb" , name: 'thumb',
						render:function (data, type, row) {
							if(row.not_thumb == 0){
								if (row.attendance == "Present") {
									var output = row.breaks.replace(/[["|]|\]/g,'').split(/[, ]+/).pop();
									return "<center><span style='color:red;'title='Out thumb : "+ output +"' >NOT THUMB</span></center>";
								}
								return "<center>-</center>";
							}else{
								return "<center><span style='color:green;'>OK</span></center>";
							}
						} 
					},
					{ data: "note", name: "note", 
						render:function (data, type, row) {
							if(data == null){
								return "<center>-</center>";
							}else{
								return "<center>"+ data +"</center>";
							}
						}
					},
					{ data: "breaks", name: "breaks", 
						render:function (data, type, row) {
							var d = JSON.parse(row.breaks);
							var breakLenght = d.length;
							tds = "";
							d.forEach(function(breakData) {
								// console.log(breakData);
								tds += '<tr>'+
											'<td>'+breakData[0]+'</td> '+
											'<td>'+(breakData[1] == undefined ? "" :  breakData[1])+'</td> '+
											'<td>'+(breakData[2] == undefined ? "" :  breakData[2])+'</td>'+
										'</tr>';
							});
							return breakLenght > 0 ? '<center><button type="button" class="btn btn-warning btn-xs" data-toggle="collapse" data-target="#demo'+row.id+'">Breaks Details</button>'+
									'<div class="collapse" id="demo'+row.id+'" ><table class="table small" style="margin-bottom:0px">'+
										'<tr>'+
											'<th>Out</th>'+
											'<th>In</th>'+
											'<th>Time</th>'+
										'</tr>'
										+tds+
								'</table></div></center>' : "<center>-</center>" ;
						}
					}/*,
					{ data: "LE", name: "LE" },
					{ data: "OT", name: "OT" }*/
				],
				"order": [[ 2, "asc" ]],
				drawCallback: function (settings) {
					var api = this.api();
					var rows = api.rows({ page: 'current' }).nodes();
					var last = null;

					api.column(2, { page: 'current' }).data().each(function (group, i) {
						if(last !== group){
							$(rows).eq(i).before(
								'<tr class="group"><td colspan="12" style="BACKGROUND-COLOR:lightgray;font-weight:700;color:#00000;" role="row">' + group  + '</td></tr>'
							);
							last = group;
						}
					});
				}
			});
		} ); 
		$("#resetDateFilter").click(function(){
			// $("#min-date").val('');
			// $("#max-date").val('');
			setDate();
			table.draw();
		});

		$('.input-daterange input').each(function() {
			$(this).datepicker('clearDates');
		});
		setDate();
		$.fn.dataTable.ext.search.push(
			function(settings, data, dataIndex) {
				var min = $('#min-date').val();
				var max = $('#max-date').val();
				var createdAt = data[0] || 0; // Our date column in the table		
				if (
					(min == "" || max == "") ||
					(moment(createdAt).isSameOrAfter(min) && moment(createdAt).isSameOrBefore(max))
				) {
					return true;
				}
				return false;
			}
		);
    	// Re-draw the table when the a date range filter changes
		$('.date-range-filter').change(function() {
			table.draw();
		});
    
		$('#my-table_filter').hide();

		$('#import_file').change(function() {
			var ext = $('#import_file').val().split('.').pop().toLowerCase(); 
				if($.inArray(ext, ['csv']) == -1) { 
					alert('invalid extension!');
					$('#import_file').val('');
					return false;
				}else{
					$("#csv-upload").click();
				}
				
		});
		function setDate() {
			var subtractDate = (moment().weekday() == 1 ? -2 : -1);
			var ydate = moment().add(subtractDate, 'days').format('YYYY-MM-DD');
			$("#min-date").val(ydate);
			$("#max-date").val(moment().format('YYYY-MM-DD'));
			// table.draw();
		}

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
@endsection