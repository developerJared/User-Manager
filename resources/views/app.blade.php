<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Netlab Management</title>
    <link href="/css/all.css?{{\Carbon\Carbon::now()}}"  rel="stylesheet" >

    <script>
        if(window.module != null && window.module.exports != null){
            module.exports = null;
        }


    </script>
    <script src="/js/jquery.js" onload="(window.module != null)&& (window.$ = window.jQuery = module.exports);"></script>

    <script src="/js/all.js"></script>

    <script src="/js/jsvalidation.js"></script>

</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-brand" onclick="window.location='{{ url("site/users") }}'">Netlab User Management</div>
        <button id="nav-exit" class="btn btn-danger" style = "float: right;" onclick="window.close()" >Exit <span class="glyphicon glyphicon-remove"></span></button>
        <button id="nav-back" class="btn btn-info" style = "float: right; width:100px; margin-right: 20px;" > <span class="glyphicon glyphicon-arrow-left"></span> Back</button>
        <span style = "color:white;float: right; width:100px; margin-right: 20px;">Logged in as : {{session('user')['firstName']}} {{session('user')['lastName']}}</span>
        <div style = "float: right; color: #D0D0D0; width:200px;">

                @section('navbar')
                @show

        </div>
    </div>
</nav>

<nav class="navbar" style="padding-top: 50px; position: fixed; top: 0; left: 0; right: 0;">
    @include('flash::message')
</nav>

<div class="container" style = "padding-top: 80px">
    @yield('content')
</div>

        <script>
            $(document).ready(function () {

                $('div.alert.alert-danger').delay(4000).slideUp(1000);
                $('div.alert.alert-success').delay(2000).slideUp(1000);
                $('#nav-back').click(function(){
                    parent.history.back();
                    return false;
                });
                $('#nav-exit').click(function(){
                   window.close();
                });
            });
        </script>

@yield('footer')

</body>
</html>