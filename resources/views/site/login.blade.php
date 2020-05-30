<!-- app/views/login.blade.php -->

<!doctype html>
<html>
<head>
    <title>Netlab Management Login</title>
    <link href="/css/all.css" rel="stylesheet">
</head>
<body>
<div>@include('flash::message')</div>
<p>{{$packhouse}} - {{$lab}}</p>
<div style="text-align:center; position: relative; top: 50%; transform: translateY(50%);">
    <div class="container" style="width: 40%">
        {!! Form::open(array('action' => 'PHPSite\AuthenticateController@authenticate', 'class'=>'form')) !!}
        <h1>User Management Login</h1>
        <div class="form-group">
            {!! Form::label('username', 'Username',array('class'=>'h3')) !!}
            {!! Form::text('username', Input::old('username'), array('placeholder' => 'Enter Username','class'=>'form-control')) !!}
        </div>
        <div class="form-group">
            {!! Form::label('password', 'Password',array('class'=>'h3')) !!}
            {!! Form::password('password',array('class'=>'form-control')) !!}
        </div>
        <div class="form-group" style = "padding-top: 30px;">
         {!! Form::submit('Login',array('class'=>'btn-primary form-control')) !!}
        </div>

        {!! Form::close() !!}
    </div>
</div>
</body>
</html>