@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/css/bootstrapValidator.min.css">
@endsection

@section('content')
@if (Auth::check())
<form id="form" name="form" class="form-inline" action="{{route('report')}}" method="POST">
    {{csrf_field()}}
    <div class="form-group">
            
        <label for="sel1">Select list:</label>
        <select class="form-control" id="sel1" name="employee" required>
            <option value="">Select Employee</option>
            @foreach($employees as $emploee)
                <option value="{{$emploee->name}}">{{$emploee->name}}</option>
            @endforeach
        </select>      
        <div class="form-group">
            <label for="startDate">Start Date</label>
            <input id="startDate" name="startDate" type="text" class="form-control" required/>
            &nbsp;
            <label for="endDate">End Date</label>
            <input id="endDate" name="endDate" type="text" class="form-control" required/>
        </div>
        <input type="submit" class="btn btn-primary" value="Search" />
    </div>
</form>
    {{-- @if($empDatas->isNotEmpty()) --}}
        <div class="panel panel-primary">
            <!-- Default panel contents -->
        <div class="panel-heading"><b>{{$empDatas == null ? 'List' : $empDatas[0]->name}}</b> <p class="pull-right"><b>From : </b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }}  |  <b>To : </b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} | <b>Total Late Time : </b><span class="badge">{{isset($totalLETime) ? $totalLETime : ""}}</span></p></div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr class="bg-info">
                            <th width="14%">NO.</th>
                            <th width="14%">Date</th>
                            <th width="14%">Entry Time</th>
                            <th width="14%">Late Entry</th>
                            <th width="14%">Exit Time</th>
                            <th width="30%">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1 @endphp
                        @forelse($empDatas as $empData)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{date('d-m-Y', strtotime($empData->date))}}</td>
                                <td>{{$empData->officeIn}}</td>
                                <td>{{$empData->LE}}</td>
                                <td>{{$empData->officeOut}}</td>
                                <td>{{$empData->note}}</td>
                            </tr>
                            @php $i++ @endphp
                        @empty
                            <tr>
                                <td colspan="4" align="center">No Data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- List group -->
            <div class="list-group">
                <a href="#" class="list-group-item">{{$empDatas == null ? 'List' : $empDatas[0]->name}} 
                    <p class="pull-right"><b>Total Late Time : </b>
                        <span class="badge">{{isset($totalLETime) ? $totalLETime : ""}}</span>
                    </p>
                </a>
            </div>
        </div>
    {{-- @endif --}}
@endif
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js"> </script>
    <script>
    var bindDateRangeValidation = function (f, s, e) {
    if(!(f instanceof jQuery)){
			console.log("Not passing a jQuery object");
    }
  
    var jqForm = f,
        startDateId = s,
        endDateId = e;
  
    var checkDateRange = function (startDate, endDate) {
        var isValid = (startDate != "" && endDate != "") ? startDate <= endDate : true;
        return isValid;
    }

    var bindValidator = function () {
        var bstpValidate = jqForm.data('bootstrapValidator');
        var validateFields = {
            startDate: {
                validators: {
                    notEmpty: { message: 'This field is required.' },
                    callback: {
                        message: 'Start Date must less than or equal to End Date.',
                        callback: function (startDate, validator, $field) {
                            return checkDateRange(startDate, $('#' + endDateId).val())
                        }
                    }
                }
            },
            endDate: {
                validators: {
                    notEmpty: { message: 'This field is required.' },
                    callback: {
                        message: 'End Date must greater than or equal to Start Date.',
                        callback: function (endDate, validator, $field) {
                            return checkDateRange($('#' + startDateId).val(), endDate);
                        }
                    }
                }
            },
          	customize: {
                validators: {
                    customize: { message: 'customize.' }
                }
            }
        }
        if (!bstpValidate) {
            jqForm.bootstrapValidator({
                excluded: [':disabled'], 
            })
        }
      
        jqForm.bootstrapValidator('addField', startDateId, validateFields.startDate);
        jqForm.bootstrapValidator('addField', endDateId, validateFields.endDate);
      
    };

    var hookValidatorEvt = function () {
        var dateBlur = function (e, bundleDateId, action) {
            jqForm.bootstrapValidator('revalidateField', e.target.id);
        }

        $('#' + startDateId).on("dp.change dp.update blur", function (e) {
            $('#' + endDateId).data("DateTimePicker").setMinDate(e.date);
            dateBlur(e, endDateId);
        });

        $('#' + endDateId).on("dp.change dp.update blur", function (e) {
            $('#' + startDateId).data("DateTimePicker").setMaxDate(e.date);
            dateBlur(e, startDateId);
        });
    }

    bindValidator();
    hookValidatorEvt();
};


$(function () {
    var sd = new Date(), ed = new Date();
  
    $('#startDate').datetimepicker({ 
      pickTime: false, 
      format: "YYYY-MM-DD", 
      defaultDate: sd, 
      maxDate: ed,
      daysOfWeekDisabled: [0]
    });
  
    $('#endDate').datetimepicker({ 
      pickTime: false, 
      format: "YYYY-MM-DD", 
      defaultDate: ed, 
      minDate: sd,
      daysOfWeekDisabled: [0]
    });

    //passing 1.jquery form object, 2.start date dom Id, 3.end date dom Id
    bindDateRangeValidation($("#form"), 'startDate', 'endDate');

});
    </script>
@endsection