@extends('app')

@section('content')

    <h1>Add User</h1>
    <br/>
    <!-- if there are login errors, show them here -->
    <div>
        @include('flash::message')
    </div>
    {!! Form::open(array('action' => 'PHPSite\UsersController@createUser', 'id'=>'user-form', 'role'=>'form', 'class'=>'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="col-sm-6">
            <div>
                <div class="form-group">
                    {!! Form::label('firstName', 'First Name',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('firstName',null,array('placeholder' => 'First Name','class'=>'form-control')) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('lastName', 'Last Name',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('lastName',null,array('placeholder' => 'Last Name','class'=>'form-control')) !!}
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <div class="form-group">
                    {!! Form::label('username', 'User Name',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('username',null,array('placeholder' => 'Preferred Username','class'=>'form-control')) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('password', 'Password',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        <input class="form-control" name="password" type="password" value="" id="password">
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('password2', 'Confirm',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        <input class="form-control" name="password2" type="password" value="" id="password2">
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('pin', 'Pin',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        <input class="form-control" name="pin" type="password" value="" id="pin">
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('pin2', 'Confirm',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        <input class="form-control" name="pin2" type="password" value="" id="pin2">
                    </div>
                </div>
            </div>

        </div>

        <div class="col-sm-6">
            <div>
                <div class="form-group">
                    {!! Form::label('address', 'Address',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('address',null,array('placeholder' => 'Address','class'=>'form-control')) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('phone', 'Phone Number',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('phone',null,array('placeholder' => 'Phone Number','class'=>'form-control')) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('mobile', 'Mobile',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('mobile',null,array('placeholder' => 'Mobile','class'=>'form-control')) !!}
                    </div>
                </div>
            </div>
           <hr>
            <div >

                <div class="form-group">
                    {!! Form::label('current_lab', 'Assigned Lab',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('current_lab',null,array('placeholder' => 'Assigned Lab','class'=>'form-control')) !!}
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('roles_id', 'Role ID',array('class'=>'col-sm-3 control-label')) !!}
                    <div class="col-sm-9">
                        {!! Form::text('roles_id',Input::old('roles_id'),array('placeholder' => 'Role ID','class'=>'form-control')) !!}
                    </div>
                </div>

                <hr/>
            </div>
        </div>

    </div>
    <div class="row">
        <div style="float: right">
            <button type="button" class="btn btn-danger" onclick="window.location='{{ url("site/users") }}'">
                Cancel
            </button>
            {!! Form::submit('Add',['id'=>'add_btn','class'=>'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#add_btn").click(function (event) {
                //validate PIN if user in role 3
                if (!confirm("Are you sure you want to add user?")) {
                    event.preventDefault();
                }
            });

            $('#user-form').validate({
                rules: {
                    firstName: {
                        required: true
                    },
                    lastName: {
                        required: true
                    },
                    username: {
                        minlength: 4,
                        required: true
                    },
                    password: {
                        minlength: 4,
                        required: true
                    },
                    password2: {
                        equalTo: '#password'
                    },
                    pin: {
                        minlength: 4,
                        required: true
                    },
                    pin2: {
                        equalTo: '#pin'
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    element
                            .text('OK!').addClass('valid')
                            .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
            });

        });
    </script>
@stop
@section('footer')
@stop
