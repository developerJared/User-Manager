@extends('app')
@section('navbar')
    @parent

@stop

@section('content')

    <div class="container-fluid">
        <p>
        <h3 style="text-align: center"><strong> Add a user login so that one can access the NL2 software </strong>
        </h3> </p>
        <button type="button" class="btn btn-primary" style="display: block; width: 100%;height:50px;"
                onclick="window.location='{{ url("site/addUser") }}'"><span style="font-size: x-large">Add a User</span>
        </button>
        <br/>
        <br/>

        <p>
        <h3 style="text-align: center"><strong> Edit an existing user including Staff Test Access </strong></h3> </p>
        <button type="button" id="editUser" class="btn btn-primary" style="display: block; width: 100%;height:50px;">
            <span style="font-size: x-large">Edit User</span>
        </button>
        <br/>
        <br/>

    </div>



@section('footer')
    <script>
        $(document).ready(function () {

            $("#editUser").click(function () {
                //  var id = available_users.value;
                window.location.href = "editUsers";
            });

            $("#editUserTestGroups").click(function () {
                var id = available_users.value;
                window.location.href = "editUserTestGroups/" + id;
            });
        });
    </script>
@stop