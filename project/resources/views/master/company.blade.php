@extends('layouts.app')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" />
@endsection
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Company Details</div>
            <div class="panel-body">
                    <form action="{{ route('company.store') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="company_id" id="company_id" value="0">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name')}}" placeholder="Enter Company name" required autofocus>
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email">Email address:</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Company Email" value="{{ old('email')}}"  required>
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        </div>
                        <div class="form-group {{ $errors->has('startTime') ? 'has-error' : '' }}">
                            <label for="">OfficeIn Time</label>
                            <div class='input-group date datetimepicker3'>
                                <input type='text' class="form-control" name="startTime" id="startTime" value="{{ old('startTime')}}" required/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-time"></span>
                                </span>
                            </div>
                            <span class="text-danger">{{ $errors->first('startTime') }}</span>
                        </div>
                        <div class="form-group {{ $errors->has('endTime') ? 'has-error' : '' }}">
                            <label for="">OfficeOut Time</label>
                            <div class='input-group date datetimepicker3'>
                                <input type='text' class="form-control" name="endTime" id="endTime" value="{{ old('endTime')}}" required/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-time"></span>
                                </span>
                            </div>
                            <span class="text-danger">{{ $errors->first('endTime') }}</span>
                        </div>
                        <div class="form-group {{ $errors->has('dutyTime') ? 'has-error' : '' }}">
                            <label for="">Total Working Time <small>(HH:MM)</small></label>
                            <input type='text' class="form-control" id="dutyTime" name="dutyTime"  value="{{ old('dutyTime')}}"  placeholder="HH:MM" required/>
                            <span class="text-danger">{{ $errors->first('dutyTime') }}</span>
                        </div>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                        </div>
                    </form>

            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong class="panel-title">Company Details</strong>
                @if(count($company)>0) <button type="button" name="edit" id="edit" class="pull-right btn btn-info btn-sm" data-company="{{ $company }}">Edit Info</button> @endif
            </div>
            <div class="panel-body">
                @if(count($company)>0)
                    <legend class="text-primary">{{ $company->name }}</legend>
                    <address class="">
                        <strong>Email:</strong> {{ $company->email }}<br>
                        <strong>OfficeIn:</strong> {{ date("g:i A",strtotime($company->startTime)) }}<br>
                        <strong>OfficeOut:</strong> {{ date("g:i A",strtotime($company->endTime)) }}<br>
                        <strong>DutyHr.:</strong> {{ $company->dutyTime }}<br>
                    </address>
                @else
                    <div class="alert alert-info">
                        <h3>Sorry...No any company data registered with us.</h3>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">
           $(function () {
               $('.datetimepicker3').datetimepicker({
                   format: 'LT',
               });
           });
           function convertTime(timefield){
               var time = timefield.split(":");
               var hours = time[0] > 12 ? time[0] - 12 : time[0];
               var am_pm = time[0] >= 12 ? "PM" : "AM";
               hours = hours < 10 ? "" + hours : hours;
               var minutes = time[1] < 10 ? "" + time[1] : time[1];
               var seconds = time[2] < 10 ? "" + time[2] : time[2];

               time = hours + ":" + minutes + " " + am_pm;
               return time;
           }
           $('#edit').click(function(){
               var company=$(this).data('company');
               $("#company_id").val(company.id);
               $('#name').val(company.name);
               $('#email').val(company.email);
               var start= convertTime(company.startTime);
               $('#startTime').val(start);
               var end=convertTime(company.endTime);
               $('#endTime').val(end);
               $('#dutyTime').val(company.dutyTime);
           })
       </script>

@endsection
