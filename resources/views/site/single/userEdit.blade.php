@extends('app')

@section('content')
        <!--We have a user model here-->
<h1>Edit User</h1>
<br/>
<!-- if there are login errors, show them here -->

{!! Form::open(array('action' => array('PHPSite\UsersController@store'), 'id'=>'user-form', 'role'=>'form', 'class'=>'form-horizontal')) !!}
{!! Form::text('id',$user['id'],array('class'=>'hidden')) !!}
        {!! Form::number('samplerGroupId',$samplerGroupId,array('class'=>'hidden')) !!}
        {!! Form::number('labTechGroupId',$labTechGroupId,array('class'=>'hidden')) !!}
<?php $Pin_Error = Session::get('Pin_Error'); ?>
@if(! empty($Pin_Error))
<div id = "Error" class="hidden" ><?php echo $Pin_Error?></div>
@endif
<div class="container-fluid">
    <div class="col-sm-6">
        <div>
            <div class="form-group">
                {!! Form::label('firstName', 'First Name',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('firstName',$user['firstName'],array('placeholder' => 'First Name','class'=>'form-control')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('lastName', 'Last Name',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('lastName',$user['lastName'],array('placeholder' => 'Last Name','class'=>'form-control')) !!}
                </div>
            </div>
        </div>
        <hr>
        <div>
            <div class="form-group">
                {!! Form::label('username', 'User Name',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('username',$user['username'],array('placeholder' => 'Preferred Username','class'=>'form-control')) !!}
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-info" id="psw_reset">
                    Reset Password
                </button>
                <button type="button" class="btn btn-info" id="pin_reset">
                    Reset Pin
                </button>
            </div>
            <div id="psw" style="display: none;">
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
            </div>
            <div id="pin" style="display: none;">
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
    </div>
    <div class="col-sm-6">
        <div>
            <div class="form-group">
                {!! Form::label('address', 'Address',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('address',$user['address'],array('placeholder' => 'Address','class'=>'form-control')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('phone', 'Phone Number',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('phone',$user['phone'],array('placeholder' => 'Phone Number','class'=>'form-control')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('mobile', 'Mobile',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('mobile',$user['mobile'],array('placeholder' => 'Mobile','class'=>'form-control')) !!}
                </div>
            </div>
        </div>
        <hr>
        <div>
            <div class="form-group">
                {!! Form::label('current_lab', 'Assigned Lab',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('current_lab',$user['current_lab'],array('placeholder' => 'Assigned Lab','class'=>'form-control')) !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('roles_id', 'Role ID',array('class'=>'col-sm-3 control-label')) !!}
                <div class="col-sm-9">
                    {!! Form::text('roles_id',$user['roles_id'],array('placeholder' => 'Role ID','class'=>'form-control')) !!}
                </div>
            </div>

            <div class="form-group" >

                {!! Form::label('active', 'Active') !!}
                {!! Form::checkbox('active',1,$user['active']) !!}
               <!---------------------------------------------------
                Removed until we can be rid of the staff_type_id in legacy

                <span style = "min-width:50px;"> | </span>
                {!! Form::label('is_Sampler', 'Sampler') !!}
                {!! Form::checkbox('is_Sampler',1,$isSampler) !!}
                <span style = "min-width:50px;"> | </span>
                {!! Form::label('is_LabTech', 'Lab Technician') !!}
                {!! Form::checkbox('is_LabTech',1,$isLabTech) !!}
                        -------------------------------------------->

            </div>

            <hr/>
        </div>
    </div>
</div>
<div class="row">-
    <div style="float: right">
        <button type="button" class="btn btn-danger" onclick="window.location='{{ url("site/users") }}'">
            Cancel
        </button>
        {!! Form::submit('Save',['id'=>'add_btn','class'=>'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
</div>

<script>
    $(document).ready(function () {
        if ( $("#Error").length ) {
            $("#psw").toggle();
        }

        $("#add_btn").click(function (event) {
            //check if pin already exist if in role 3
            if (!confirm("Are you sure you want to save user?")) {
                event.preventDefault();
            }
        });

        $("#psw_reset").click(function () {
            $("#psw").toggle();
        });

        $("#pin_reset").click(function () {
            $("#pin").toggle();
        });

        /* $('#user-form').validate({
         debug: true,
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
         });*/
    });
</script>
@stop
@section('footer')
@stop