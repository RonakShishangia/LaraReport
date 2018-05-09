<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title></title>

        <style>
        table {
            border-collapse: collapse;
            border-spacing: 20px;
        }
        table tr td{
            padding:5px;
        }
        </style>
    </head>
    <body>
        {{-- {{ dd($attendanceReportDatas) }} --}}
        <h1>NKonnect Infoway Pvt. Ltd.</h1>
        <h3>Daily Attendance Report Notification</h3>
        <table border="1">
            <thead>
                <tr>
                    <td>Name</td>
                    <td colspan="6">{{ $attendanceReportDatas->name }}</td>
                </tr>
                <tr>
                    <td>Department</td>
                    <td colspan="6">{{ $attendanceReportDatas->department }}</td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td colspan="6">{{ date("d-m-Y",strtotime($attendanceReportDatas->date)) }}</td>
                </tr>
                <tr>
                    <td>Attendance</td>
                    <td colspan="6">{{ $attendanceReportDatas->attendance }}</td>
                </tr>
                @if($attendanceReportDatas->attendance == "Present")
                    <tr>
                        <th colspan="7">Attendence Report</th>
                    </tr>
                @endif
            </thead>
            @if($attendanceReportDatas->attendance == "Present")
                <thead style="background:#f0ad4e">
                    <tr>
                        <td>OfficeIn</td>
                        <td>OfficeOut</td>
                        <td>Break Time</td>
                        <td>Worked Time</td>
                        <td>Total Time</td>
                        <td style="background:{{strpos($attendanceReportDatas->LE,'-')!==false ? '#00ff00' : '#ff0000' }}">Late/Early</td>
                        <td style="background:{{strpos($attendanceReportDatas->OT,'-')!==false ? '#ff0000' : '#00ff00' }}">OT</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $attendanceReportDatas->officeIn }}</td>
                        <td>{{ $attendanceReportDatas->officeOut }}</td>
                        <td>{{ $attendanceReportDatas->total_break_time }}</td>
                        <td>{{ $attendanceReportDatas->worked_time }}</td>
                        <td>{{ $attendanceReportDatas->total_time }}</td>
                        <td>{{ $attendanceReportDatas->LE }}</td>
                        <td>{{ $attendanceReportDatas->OT }}</td>
                    </tr>
                </tbody>
            @endif
        </table>
    </body>
</html>
