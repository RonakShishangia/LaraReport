@extends('layouts.app')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/jquery.dataTables.min.css')}}" >
	<link rel="stylesheet" href="{{ asset('css/select.dataTables.min.css')}}" >
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css" >
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.dataTables.min.css" >
@endsection

@section('content')
	<div class="row text-center">
    	<form action="{{ URL::to('importExcel') }}" class="form-inline" method="post" enctype="multipart/form-data">
			<div class="col-md-6">
				{{ csrf_field() }}
				<!-- <a href="{{ URL::to('downloadExcel/xls') }}"><button class="btn btn-success">Download Excel xls</button></a>
				<a href="{{ URL::to('downloadExcel/xlsx') }}"><button class="btn btn-success">Download Excel xlsx</button></a>
				<a href="{{ URL::to('downloadExcel/csv') }}"><button class="btn btn-success">Download CSV</button></a> -->
				<input type="file" class="btn btn-primary" name="import_file" id="import_file"  required/>
			</div>
			<div class="col-md-6">
				<button class="btn btn-primary">Import File</button>
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

	<table id="example" class="display" width="100%" cellspacing="0">
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
            {{--<th>LE</th>
            <th>OT</th>--}}
        </tr>
    </tfoot>
</table>

@endsection

@section('script')		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js" charset="utf-8"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js" charset="utf-8"></script>
	<script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('js/dataTables.select.min.js') }}"></script>


	<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
	<script src="https://cdn.datatables.net/responsive/2.2.1/js/dataTables.responsive.min.js"></script>
	<script>
    /* Formatting function for row details - modify as you need */
    function format ( d ) {
        // `d` is the original data object for the row
        console.log(d.breaks);
        console.log(JSON.parse(d.breaks));
        return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
            '<tr>'+
                '<td>Breaks:</td>'+
                '<td>'+JSON.parse(d.breaks)+'</td>'+
            '</tr>'+
        '</table>';
    }
    
    $(document).ready(function() {
        var table = $('#example').DataTable( {
            "data":  <?php echo $data; ?> ,
            data: <?php echo $data; ?> ,
            dom: 'Bfrtip',
            pageLength: 25,
            buttons: [
                'pageLength', 'csv', 'excel', 'pdf', 'print'
            ],
            "columns": [
                {
                    "class":          'details-control',
                    "orderable":      false,
                    "data":           null,
                    "defaultContent": ''
                },
                { "data": "date", name: 'date' },
                { "data": "name", name: 'name' },
                { "data": "department", name: 'department' },
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
                { data: "thumbs" , name: 'thumbs',
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
                }/*,
                { data: "LE", name: "LE" },
                { data: "OT", name: "OT" }*/
            ]
        } );
        
        // Add event listener for opening and closing details
        $('#example tbody').on('click', 'td.details-control', function () {
            var tr = $(this).parents('tr');
            var row = table.row( tr );
    
            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                row.child( format(row.data()) ).show();
                tr.addClass('shown');
            }
        });
    });
</script>
@endsection