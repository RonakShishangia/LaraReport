@extends('layouts.app')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/css/bootstrapValidator.min.css">
@endsection
@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">Company Details</div>
            <div class="panel-body">
                @if($company > 0)
                    <h3>Column 1</h3>
                    <p>Lorem ipsum dolor..</p>
                    <p>Ut enim ad..</p>
                @else
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Company name" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="email">Email address:</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter Company Email">
                        </div>
                        <div class="form-group">
                            <label for="">End Time</label>
                            <div class='input-group date datetime' id='startDate'>
                                <input type='text' class="form-control col-md-6" name="startDate" required/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-time"></span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">End Time</label>
                            <div class='input-group date datetime' id='endDate'>
                                <input type='text' class="form-control" name="endDate" required/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-time"></span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Total Working Time</label>
                            <div class='input-group date datetime' id='company_work_time'>
                                <input type='text' class="form-control" name="company_work_time" required/>
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-time"></span>
                                </span>
                            </div>
                        </div>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="reset" class="btn btn-default">Reset</button>
                        </div>
                    </form> 
                    {{-- <a href="{{route('master.create')}}" class="btn btn-primary">Add Company Detailes</a> --}}
                @endif
            </div>
            <div class="panel-footer">
                @if($company > 0)
                    <a href="" class="btn btn-primary">Edit</a>
                @endif
            </div>
        </div>    
    </div>
</div>
@endsection
@section('script')
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.3/js/bootstrap-datetimepicker.min.js"> </script>

    <script>
            
        $(function () {
            $('#company_work_time').datetimepicker({
                format: 'LT'
            });
            $('#endDate').datetimepicker({
                format: 'LT'
            });
            $('#startDate').datetimepicker({
                format: 'LT'
            });
        });
    </script>
@endsection