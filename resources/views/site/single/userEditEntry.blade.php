@extends('app')
@section('navbar')

@stop

@section('content')

    <div class="container-fluid">
        <div class="page-header">
            <h1>Select User to Edit</h1>

        </div>
        <div class="row">
            <div class="col-md-5">
                <select id="available_users" data-width="85%" autofocus>
                    @foreach($users as $user)
                        <option value="{{$user['id']}}">
                            <a onclick="window.location='{{ url("site/editUser/".$user['id']) }}'">{{ $user['firstName'] }} {{ $user['lastName'] }}</a>
                            @if($user['active'] == 0)
                                --INACTIVE
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7">
                @if($layout == "info")
                <button id="editUser" type="button" class="btn btn-primary">Edit User</button>
                @elseif($layout == "access")
                <button id="editUserTestGroups" type="button" class="btn btn-primary">Edit Test Access</button>
                <button id="editUserCutters" type="button" class="btn btn-primary">Edit Cutters Access</button>
                <button id="editUserFunctions" type="button" class="btn btn-primary">Edit Functions Access</button>
                @endif
            </div>
        </div>

    </div>
@stop
@section('footer')
    <script>
        $(document).ready(function () {
            $("#available_users").select2();

            $("#editUser").click(function () {
                var id = available_users.value;
                window.location.href = "editUser/" + id;
            });

            $("#editUserTestGroups").click(function () {
                var id = available_users.value;
                window.location.href = "editUserTestGroups/" + id + "/TestError";
            });

            $("#editUserCutters").click(function () {
                var id = available_users.value;
                window.location.href = "userCutters/" + id;
            });

            $("#editUserFunctions").click(function () {
                var id = available_users.value;
                window.location.href = "userFunctions/" + id;
            });
        });
    </script>
@stop