@extends('app')

@section('content')

        <!--We have a user model here-->
{!! Form::open(array('action' => 'PHPSite\UsersController@storeFunctionsAccess')) !!}
<div class="row">
    <div class="col-sm-6">
        <h1>Function Access - </h1>
        <a onclick="window.location='{{ url("/site/editUsers/access") }}'">- Staff Access</a>
    </div>

    <div class="col-sm-6" style="height: 69px; padding-top: 25px;">
        <select id="target_function" name="target_function" class="form-control" data-width="100%">
            @foreach($functions as $func)
                <option value="{{$func['id']}}">
                    {{$func['group']}}
                </option>
            @endforeach
        </select>
    </div>
</div>

<br/>
<!-- if there are login errors, show them here -->

<div class="container" style="margin-top: 30px; ">

    <div>
        <span id="selectedFunction" style="font-weight: bold; font-size: 2em"></span>
    </div>
    <br>
    <br>
    <div style="width:96%;">
        <div class="col-md-3"><strong>User</strong></div>
        <div class="col-md-4"><strong>Date/Time Assigned</strong></div>
        <div class="col-md-4"><strong>Assigned By</strong></div>
        <div class="col-md-1"><strong>Assigned</strong></div>
    </div>
    <div style="width:100%; height:500px; overflow:auto;">
        <table class="table">
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="col-md-3">{{$user['firstName']}} {{$user['lastName']}}</td>
                    <td class="col-md-4"><span id="assignedDate" data="{{$user['id']}}">Unassigned</span></td>
                    <td class="col-md-4"><span id="assignedBy" data="{{$user['id']}}">Unassigned</span></td>
                    <td class="col-md-1">
                        {!! Form::checkbox( $user['id'],null,$checked,['class' => 'check site-inputcheckbox', 'id' => $counter++]) !!}
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>

</div>


<hr/>
<div class="row">
    <div style="float: right">
        <button type="button" class="btn btn-danger" onclick="window.location='{{ url("site/users") }}'">Cancel
        </button>
        {!! Form::submit('Save',['id'=>'save_btn','class'=>'btn btn-primary']) !!}
    </div>
    <div style="float: right; padding-right: 50px;">
        <button id="reset_all" type="button" class="btn btn-warning">Reset</button>
    </div>
</div>
</div>

{!! Form::close() !!}
@stop
@section('footer')
    <script>
        var active_groups = <?php echo json_encode($active_groups); ?> //array of objects

                function setChecked(elem) {
                    var checkbox = elem;
                    var checkedIt = false;
                    active_groups.forEach(function (value) {
                        if (value['users_id'] != checkbox.name) {
                            return;
                        }
                        if (value['groups_id'] == $("select option:selected").val()) {
                            checkedIt = true;
                            $("[data=" + value.users_id + "][id='assignedDate']").text(value.updated_at);
                            if (value.updated_by != null) {
                                $("[data=" + value.users_id + "][id='assignedBy']").text(value.updated_by);
                            } else {
                                $("[data=" + value.users_id + "][id='assignedBy']").text(" ");
                            }
                            return checkbox.checked = true;
                        }
                    });
                    if (checkedIt == false) {
                        checkbox.checked = false;
                    }
                }

        $("td input").each(function () {
            setChecked(this);
        });

        $(document).ready(function () {

            $("#selectedFunction").text($("select option:selected").text());

            $("#target_function").select2()
                    .change(function () {
                        $("#selectedFunction").text($("select option:selected").text());
                        $("td input").each(function () {
                            setChecked(this);
                        });
                    });

            $("#save_btn").click(function (event) {
                if (!confirm("Are you sure you want to save functions access?")) {
                    event.preventDefault();
                }
            });
        });

    </script>
@stop