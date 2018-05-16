<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title></title>

    <style>
    table {
        border-collapse: collapse;
        border-spacing: 20px;
        align:center;
    }
    table tr td, table tr th{
        padding:5px;
    }
    </style>
</head>
<body>

    @if(count($empDatas) > 0)
        <h2>{{ $empDatas[0]->employee->company->name }}</h2>
        <table border="1">
            <thead>
                <tr>
                    <td colspan="2">Employee Name</td>
                    <td colspan="11">{{$empDatas == null ? 'List' : $empDatas[0]->employee->name}}</td>
                </tr>
                <tr>
                    <td colspan="2">Department</td>
                    <td colspan="11">{{$empDatas == null ? 'List' : $empDatas[0]->employee->department->name}}</td>
                </tr>
                <tr>
                    <th colspan="13">
                        {{ date('d-m-Y', strtotime($startDate))." To ".date('d-m-Y', strtotime($endDate)) }} Attendance Report
                    </th>
                </tr>
            </thead>
            <thead style="background:#d9edf7;">
                <tr>
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
                $tempEntryTimeArr = [];     $tempExitTimeArr = [];
                $tempTotalTimeArr = [];
                $tempTotalWorkedTimeArr = [];
                $tempTotalBreakTimeArr = [];    $officeBreakTotal=[];
                $totalLeaveArr = [];
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
                    <tr style="color:{{ $empData->attendance=='Absent' ? '#ff0000' : '' }}">
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
                    $tempTotalWorkedTimeArr[] = $empData->worked_time;
                    if($empData->day != 'Sunday'){
                        $empData->attendance != "Absent" ? $tempEntryTimeArr[] =  strtotime($empData->officeIn) : "";
                        $empData->attendance != "Absent" ? $tempExitTimeArr[] =  strtotime($empData->officeOut) : "";
                        $tempTotalTimeArr[] =  $empData->total_time;
                        $tempTotalBreakTimeArr[] = $empData->total_break_time;
                        $tempDutyTime[]=$empData->employee->company->dutyTime;
                        $empData->attendance!="Absent" ?  $officeBreakTotal[]=$empData->employee->company->breakTime : "";
                        $empData->attendance!="Absent" ?  $empData->not_thumb==0 ? $tempNotThumb[]=date('d-m-Y', strtotime($empData->date)) : "" : "";
                        if(strpos($empData->LE, '-') !== false){
                            $EEdata = str_replace('-', '', $empData->LE);
                            $tempEarlyEntryTimeArr[] = strtotime($EEdata);
                        }else{
                            $LEdata = str_replace('-', '', $empData->LE);
                            $tempLateEntryTimeArr[] =  strtotime($LEdata);
                        }

                        if ($empData->attendance=="Absent") {
                            $totalLeaveArr[] = date('d-m-Y', strtotime($empData->date));
                        }
                    }
                    @endphp
                @empty
                    <tr>
                        <td colspan="12" align="center"></td>
                    </tr>
                @endforelse
                <tfoot style="background:#d9edf7;">
                    <tr>
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
                </tfoot>
            </tbody>
        </table>
        <br>
        <h2>Report Card |
            <small>
                <u>From</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($startDate)) : "" }}</b>
                <u>To</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($endDate)) : "" }}</b>
            </small>
        </h2>
        <table border="1">
            @php
            $avgEntry=date('H:i:s', array_sum($tempEntryTimeArr)/count($tempEntryTimeArr));
            $avgExit=date('H:i:s', array_sum($tempExitTimeArr)/count($tempExitTimeArr));
            $late=date('H:i:s', array_sum($tempLateEntryTimeArr));
            $early=date('H:i:s', array_sum($tempEarlyEntryTimeArr));
            $break=TimeCalc::sumOfTime($tempTotalBreakTimeArr)." / ".TimeCalc::sumOfTime($officeBreakTotal);
            $breakDiff=TimeCalc::subOfTime(TimeCalc::sumOfTime($tempTotalBreakTimeArr),TimeCalc::sumOfTime($officeBreakTotal));
            $dutyTime=TimeCalc::sumOfTime($tempDutyTime);
            $workedTime=TimeCalc::sumOfTime($tempTotalWorkedTimeArr);
            $otlt=TimeCalc::subOfTime(TimeCalc::sumOfTime($tempTotalWorkedTimeArr),TimeCalc::sumOfTime($tempDutyTime));
            $leave=count($totalLeaveArr);
            $notThumb=count($tempNotThumb);
            @endphp
            <thead style="background:#fcf8e3;">
                <tr>
                    <th>Avg. Entry Time</th>
                    <th>Avg. Exit Time</th>
                    <th>Total Late Time</th>
                    <th>Total Early Time</th>
                    <th>Break Taken / Total Break</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>{{ $avgEntry }}</th>
                    <th>{{ $avgExit }}</th>
                    <th>{{ $late }}</th>
                    <th>{{ $early }}</th>
                    <th>{{ $break }}</th>
                </tr>
            </tbody>
            <thead style="background:#fcf8e3;">
                <tr>
                    <th>Total Duty Time</th>
                    <th>Total Worked Time</th>
                    <th>OT/Less Time</th>
                    <th>Total Leaves</th>
                    <th>Total Not Thumb</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>{{ $dutyTime }}</th>
                    <th>{{ $workedTime }}</th>
                    <th style="color:{{ strpos($otlt,'-')!==false ? 'red' : 'green' }}">{{ $otlt }}</th>
                    <th>{{ $leave==0 ? '-' : $leave }}</th>
                    <th>{{ $notThumb }}</th>
                </tr>
            </tbody>
        </table>
    </div>
@endif
</div>
</div>

</body>
</html>
