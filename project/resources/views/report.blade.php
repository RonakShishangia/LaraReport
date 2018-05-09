@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css">
    {{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/css/bootstrapValidator.min.css">
    <style media="screen">
        #tblreport tr > th {
            text-align: center;
        }
    </style>
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
{{-- {{isset($empDatas) ? $empDatas : ""}} --}}
    {{-- @if($empDatas->isNotEmpty()) --}}
        <div class="panel panel-primary">
            <!-- Default panel contents -->
        @if(count($empDatas) > 0)

        @php
        function sumOfTime($timeArr){
            $seconds = 0;
            foreach ($timeArr as $time)
            {
                list($hour,$minute,$second) = explode(':', $time);
                $seconds += $hour*3600;
                $seconds += $minute*60;
                $seconds += $second;
            }
            $hours = floor($seconds/3600);
            $seconds -= $hours*3600;
            $minutes  = floor($seconds/60);
            $seconds -= $minutes*60;
            if($seconds <= 9)
                $seconds = "0".$seconds;
            if($minutes <= 9)
                $minutes = "0".$minutes;
            if($hours <= 9)
                $hours = "0".$hours;
            return  "{$hours}:{$minutes}:{$seconds}";
        }
        @endphp

            <div class="panel-heading">
                <b>{{$empDatas == null ? 'List' : $empDatas[0]->name}}</b>
                <p class="pull-right">
                    From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                    To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
                </p>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr class="bg-info">
                            <th>NO.</th>
                            <th>Day</th>
                            <th>Date</th>
                            <th>Attendance</th>
                            <th>Entry Time</th>
                            <th>Late Entry</th>
                            <th>Exit Time</th>
                            <th>Total Time</th>
                            <th>Worked Time</th>
                            <th>Total Break Time</th>
                            <th>Thumb</th>
                            <th>OverTime</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tempEntryTimeArr = [];
                            $tempExitTimeArr = [];
                            $tempTotalTimeArr = [];
                            $tempTotalWorkedTimeArr = [];
                            $tempTotalBreakTimeArr = [];
                            $tempTotalBreakArr = [];
                            $tempEarlyEntryTimeArr = [];
                            $tempLateEntryTimeArr = [];
                            $tempOTTimeArr = [];
                            $tempLessOTTimeArr = [];
                            $tempDutyTime=[];
                            $tempOverTime=[];
                            $tempLessTime=[];
                            $tempNotThumb=[];
                            $tot = 0;
                        @endphp
                        @forelse($empDatas as $index => $empData)
                            <tr>
                                <td>{{$index+1}}</td>
                                <td>{{$empData->day}}</td>
                                <td>{{date('d-m-Y', strtotime($empData->date))}}</td>
                                <td class="{{$empData->attendance=="Absent" ? 'text-danger' : 'text-success'}}">{{$empData->attendance}}</td>
                                <td>{{$empData->officeIn=="00:00:00" ? "-" : $empData->officeIn}}</td>
                                <td class="{{ substr_count($empData->LE, '-') ? 'text-success' : "text-danger" }}">{{$empData->LE=="00:00:00" ? "-" : $empData->LE}}</td>
                                <td>{{$empData->officeOut=="00:00:00" ? "-" : $empData->officeOut}}</td>
                                <td>{{$empData->total_time=="00:00:00" ? "-" : $empData->total_time}}</td>
                                <td>{{$empData->worked_time=="00:00:00" ? "-" : $empData->worked_time}}</td>
                                <td>{{$empData->total_break_time=="00:00:00" ? "-" : $empData->total_break_time}}</td>
                                <td>{{ $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? "Not Thumb" : "-" : "-" }}</td>
                                <td>{{ $empData->OT}}</td>
                                <td>{{$empData->note}}</td>
                            </tr>
                            @php
                                $empData->attendance != "Absent" ? $tempEntryTimeArr[] =  strtotime($empData->officeIn) : "";
                                $empData->attendance != "Absent" ? $tempExitTimeArr[] =  strtotime($empData->officeOut) : "";
                                $tempTotalTimeArr[] =  $empData->total_time;
                                $tempTotalWorkedTimeArr[] = $empData->worked_time;
                                $tempTotalBreakTimeArr[] = $empData->total_break_time;
                                $tempDutyTime[]=$empData->employee->company->dutyTime;
                                
                                strpos($empData->OT,'-')!==false ? $tempLessTime[]=str_replace('-', '', $empData->OT) : $tempOverTime[]=$empData->OT;

                                $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? $tempNotThumb[]=date('d-m-Y', strtotime($empData->date)) : "" : "";

                                if(strpos($empData->LE, '-') !== false){
                                    $EEdata = str_replace('-', '', $empData->LE);
                                    $tempEarlyEntryTimeArr[] = strtotime($EEdata);
                                }else{
                                    $LEdata = str_replace('-', '', $empData->LE);
                                    $tempLateEntryTimeArr[] =  strtotime($LEdata);
                                }
                                
                                if ($empData->attendance=="Absent") {
                                    $tempTotalBreakArr[] = date('d-m-Y', strtotime($empData->date));
                                }

                            @endphp
                        @empty
                            <tr>
                                <td colspan="12" align="center"></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <legend>Report Card | <small><u>From</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($startDate)) : "" }}</b> <u>To</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($endDate)) : "" }}</b></small></legend>
                <table class="table" id="tblreport">
                    <thead class="bg-warning">
                        <tr>
                            <th>Avg. Entry Time</th>
                            <th>Avg. Exit Time</th>
                            <th>Total Late Time</th>
                            <th>Total Early Time</th>
                            <th>Total Worked Time / Total Duty Time</th>
                            <th>Total Break Time</th>
                            <th>Total Time</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>{{ date('H:i:s', array_sum($tempEntryTimeArr)/count($tempEntryTimeArr)) }}</th>
                            <th>{{ date('H:i:s', array_sum($tempExitTimeArr)/count($tempExitTimeArr)) }}</th>
                            <th>{{ date('H:i:s', array_sum($tempLateEntryTimeArr)) }}</th>
                            <th>{{ date('H:i:s', array_sum($tempEarlyEntryTimeArr)) }}</th>
                            <th>{{ sumOfTime($tempTotalWorkedTimeArr)." / ".sumOfTime($tempDutyTime) }}</th>
                            <th>{{ sumOfTime($tempTotalBreakTimeArr) }}</th>
                            <th>{{ sumOfTime($tempTotalTimeArr) }}</th>

                        </tr>
                    </tbody>
                    <thead class="bg-warning">
                        <tr>
                            <th>Less Duty Time / OT </th>
                            <th>Total Leaves</th>
                            <th>Total Not Thumb</th>
                            <th></th><th></th><th></th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th><span class='text-danger'>{{ sumOfTime($tempLessTime) }}</span> / <span class="text-success">{{sumOfTime($tempOverTime)}}</span> </th>
                            <th>{{ count($tempTotalBreakArr)==0 ? '-' : count($tempTotalBreakArr) }}</th>
                            <th>{{ count($tempNotThumb) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- List group -->
            <div class="list-group">
                {{-- {{ceil(array_sum($ExitTimeArr)/count($ExitTimeArr))}} --}}
                <a href="#" class="list-group-item">{{$empDatas == null ? 'List' : $empDatas[0]->name}}
                    <p class="pull-right">
                        From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                        To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
                    </p>
                </a>
            </div>
        </div>
        @endif
    {{-- @endif --}}
@endif
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    {{-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script> --}}
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
    //   daysOfWeekDisabled: [0]
    });

    $('#endDate').datetimepicker({
      pickTime: false,
      format: "YYYY-MM-DD",
      defaultDate: ed,
    //   minDate: sd,
    //   daysOfWeekDisabled: [0]
    });

    //passing 1.jquery form object, 2.start date dom Id, 3.end date dom Id
    bindDateRangeValidation($("#form"), 'startDate', 'endDate');

});
    </script>
@endsection
