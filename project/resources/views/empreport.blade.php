<div class="panel panel-primary" >
    @if(count($empDatas) > 0)
        <div class="panel-heading">
            <b>{{$empDatas == null ? 'List' : $empDatas[0]->employee->name}}</b>
            <p class="pull-right">
                From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
            </p>
        </div>
        <div class="panel-body">
            <button type="button" name="export" id="export" class="btn btn-info">Export to Excel</button>
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
                    <tfoot>
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
                    </tfoot>
                </tbody>
            </table>
            <form id="frmExport" action="{{ route('export-to-excel') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="startDate" value="{{ isset($startDate) ? date('d - m - Y', strtotime($startDate)) : "" }}">
                <input type="hidden" name="endDate" value="{{ isset($startDate) ? date('d - m - Y', strtotime($endDate)) : "" }}">
                <input type="hidden" name="employee_id" value="{{$empDatas == null ? '0' : $empDatas[0]->employee->id}}">
                <legend>Report Card |
                    <small>
                        <u>From</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($startDate)) : "" }}</b>
                        <u>To</u> : <b>{{ isset($startDate) ? date('d - m - Y', strtotime($endDate)) : "" }}</b>
                    </small>
                </legend>
                <table class="table" id="tblreport">
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
                    <thead class="bg-warning">
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
                            <th><input type="hidden" name="avgEntry" value="{{ $avgEntry }}"> {{ $avgEntry }}</th>
                            <th><input type="hidden" name="avgExit" value="{{ $avgExit }}"> {{ $avgExit }}</th>
                            <th><input type="hidden" name="late" value="{{ $late }}">{{ $late }}</th>
                            <th><input type="hidden" name="early" value="{{ $early }}">{{ $early }}</th>
                            <th><input type="hidden" name="break" value="{{ $break }}">{{ $break }}</th>
                        </tr>
                    </tbody>
                    <thead class="bg-warning">
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
                            <th><input type="hidden" name="dutyTime" value="{{ $dutyTime }}">{{ $dutyTime }}</th>
                            <th><input type="hidden" name="workedTime" value="{{ $workedTime }}">{{ $workedTime }}</th>
                            <th class="{{strpos($otlt,'-')!==false ? 'text-danger' : 'text-success' }}"><input type="hidden" name="otlt" value="{{ $otlt }}">{{ $otlt }}</th>
                            <th><input type="hidden" name="leave" value="{{ $leave }}">{{ $leave==0 ? '-' : $leave }}</th>
                            <th><input type="hidden" name="notThumb" value="{{ $notThumb }}">{{ $notThumb }}</th>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <div class="list-group">
            <a href="#" class="list-group-item">{{$empDatas == null ? 'List' : $empDatas[0]->name}}
                <p class="pull-right">
                    From : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($startDate)) : "" }} </b>  |
                    To : <b>{{ isset($startDate) ? date('d-m-Y', strtotime($endDate)) : "" }} </b>
                </p>
            </a>
        </div>
    @endif
</div>
<script type="text/javascript">
    $('#export').click(function(){
        $('#frmExport').submit();
    });
</script>
