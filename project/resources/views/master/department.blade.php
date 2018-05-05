@extends('layouts.app')

@section('style')
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">Department List</div>
            <div class="panel-body">    
                <table class="table">
                    <tr>
                        <th>No.</th>
                        <th>Departments</th>
                        <th>Action</th>
                    </tr>
                    @php $no = 1; @endphp
                    @forelse ($departments as $department)
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $department->name }}</td>
                        <td>
                            <a class="btn btn-primary edit" data-tr="{{ $department }}">Edit</a>
                            <a class="btn btn-danger" onclick="$('#deleteForm{{$department->id}}').submit();">Delete</a>
                            <form id='deleteForm{{$department->id}}' action="{{route('department.destroy', $department->id)}}" method="POST">
                                {{csrf_field()}}{{method_field("DELETE")}}
                            </form>
                        </td>
                    </tr>
                    @php $no++; @endphp
                    @empty
                        <tr>
                            <td colspan="3" class="text-center"> No Department</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Department Details</div>
            <div class="panel-body">
                <form action="{{ route('department.store') }}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" id="editId" name="editId" value="">
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name')}}" placeholder="Enter Company name" required autofocus>
                        <small class="text-danger">{{ $errors->first('name') }}</small>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="reset" class="btn btn-default">Reset</button>
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
        var department = $(this).data('tr');
        $('#name').val(department.name);
        $('#editId').val(department.id);
    });
</script>
@endsection