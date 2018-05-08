@extends('layouts.app')

@section('style')
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">Employee List</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Department</th>
                        <th>Action</th>
                    </tr>
                    @php $no = 1; @endphp
                    @forelse ($employees as $employee)
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $employee->user->name }}</td>
                        <td>{{ $employee->user->email }}</td>
                        <td>{{ $employee->contact }}</td>
                        <td>{{ $employee->department->name }}</td>
                        <td>
                            <a class="btn btn-primary btn-xs edit" data-tr="{{ $employee }}">Edit</a>
                            <a class="btn btn-danger btn-xs" onclick="$('#deleteForm{{$employee->id}}').submit();">Delete</a>
                            <form id='deleteForm{{$employee->id}}' action="{{route('employee.destroy', $employee->id)}}" method="POST">
                                {{csrf_field()}}{{method_field("DELETE")}}
                            </form>
                        </td>
                    </tr>
                    @php $no++; @endphp
                    @empty
                        <tr>
                            <td colspan="3" class="text-center"> No Employee</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Employee Details</div>
            <div class="panel-body">
                <form action="{{ route('employee.store') }}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" id="editId" name="editId" value="">
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name')}}" placeholder="Enter Employee name" required autofocus>
                        <small class="text-danger">{{ $errors->first('name') }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" id="pass_id">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" name="password" id="password" value="{{ old('password')}}" placeholder="Enter Password" required >
                            <small class="text-danger">{{ $errors->first('password') }}</small>
                        </div>
                    <div class="form-group{{ $errors->has('confirm_password') ? ' has-error' : '' }}" id="c_pass">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required >
                            <small class="text-danger">{{ $errors->first('confirm_password') }}</small>
                        </div>
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <select class="form-control" id="department_id" name="department_id">
                            @foreach ($departments as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>
                            @endforeach
                        </select>
                      </div>
                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}" id="email_div">
                        <label for="email">Email:</label>
                        <input type="text" class="form-control" name="email" id="email" value="{{ old('email')}}" placeholder="Enter Email" required >
                        <small class="text-danger">{{ $errors->first('email') }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('contact') ? ' has-error' : '' }}">
                        <label for="contact">Contact:</label>
                        <input type="text" class="form-control" name="contact" id="contact" value="{{ old('contact')}}" placeholder="Enter Mobile Number" required >
                        <small class="text-danger">{{ $errors->first('contact') }}</small>
                    </div>
                     <div class="pull-right">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="reset" class="btn btn-default" id="reset1">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(".edit").click(function(){
        var employee = $(this).data('tr');
        $('#editId').val(employee.id);
        $('#name').val(employee.name);
        $('#department_id').val(employee.department_id);
        $('#contact').val(employee.contact);
        $('#email').val(employee.email).prop("readonly", true);
        $("#pass_id, #c_pass").remove();
    });
</script>
@endsection
