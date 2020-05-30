@extends('app')


@section('content')
        <!--We have a user model here-->
{!! Form::open(array('action' => 'PHPSite\UsersController@storeTestAccess')) !!}
<div class="row">
    <div class="col-sm-6">
        <h1>Test Access - </h1>
        <a onclick="window.location='{{ url("site/editUsers/access") }}'">- Staff Access</a>
    </div>

    <div class="col-sm-6" style=" padding-top: 25px;">
        <label>Crop Type</label>
        <select id="available_crops" name="available_crops" class="form-control" data-width="100%">
            @foreach($crops as $crop)
                <option value="{{$crop}}">
                    {{$crop}}
                </option>
            @endforeach
        </select>
        <br>
        <br>
        <div id="kiwifruit_tests">
            <label>Test</label>
            <select id="available_kiwifruit_tests" name="available_kiwifruit_tests" class="form-control"
                    data-width="100%">
                @foreach($testTypes as $tt)
                    @if(strpos($tt['name'], 'NetlabTestType:NetlabCropType:Kiwifruit') !== false)
                        <option value="{{$tt['id']}}">
                            <?php
                            $displayed = explode(":", $tt['name']);
                            if ($displayed[2] == "Kiwifruit") {
                                switch ($displayed[3]) {
                                    case "Colour":
                                        echo "Colour Single";
                                        break;
                                    case "Colour Double":
                                        echo "Colour";
                                        break;
                                    case "Pressure":
                                        echo "Pressure Single";
                                        break;
                                    case "Pressure Double":
                                        echo "Pressure";
                                        break;
                                    default:
                                        echo "$displayed[3]";
                                        break;
                                }
                            }
                            ?>
                        </option>
                    @endif
                    @if(strpos($tt['name'], 'Kiwifruit') === false && strpos($tt['name'], 'Avocado') === false)
                        <option value="{{$tt['id']}}">
                            DM Approval
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div id="avo_tests">
            <label>Test</label>
            <select id="available_avo_tests" name="available_avo_tests" class="form-control" data-width="100%">
                @foreach($testTypes as $tt)
                    @if(strpos($tt['name'], 'NetlabTestType:NetlabCropType:Avocado') !== false)
                        <option value="{{$tt['id']}}">
                            <?php
                            $displayed = explode(":", $tt['name']);
                            if ($displayed[2] == "Avocado") {
                                echo "$displayed[3]";
                            }
                            ?>
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
</div>
<br>
<br>
<br>
<!-- if there are login errors, show them here -->

<div class="container" style="margin-top: 30px; ">
    <div id="wrapper">
        <table class="table">
            <thead>
            <td></td>
            <td>Date/Time Assigned</td>
            <td>Assigned By</td>
            @foreach($availGroups as $group)
                @if($group['id'] < 5)
                    <?php
                    $class_str = "";
                    switch ($group['id']) {
                        case 1:
                            $class_str = "site-grey";
                            break;
                        case 2:
                            $class_str = "site-yellow";
                            break;
                        case 3:
                            $class_str = "site-green";
                            break;
                        case 4:
                            $class_str = "site-red";
                            break;
                    }
                    ?>
                    <td style="width: 90px; text-align: center;" class="<?php echo $class_str; ?>">
                        <span style="background-color: #FFFFFF;"><strong> {{$group['group']}} </strong></span>
                    </td>
                @endif
            @endforeach
            </thead>

            <tbody>

            @foreach($users as $user)
                <tr>
                    <td>{{$user['firstName']}} {{$user['lastName']}}</td>
                    <td><span id="assignedDate" data="{{$user['id']}}">Unassigned</span></td>
                    <td><span id="assignedBy" data="{{$user['id']}}">Unassigned</span></td>
                    @foreach($availGroups as $group)
                        @if($group['id'] < 5 )
                            <td style="width: 120px;"> {!! Form::radio( $user['id'], $group['id'],$checked) !!}</td>
                        @endif
                    @endforeach
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
        function setClass(elem) {
            if ($(elem).prop("checked")) {
                if ($(elem).val() == 1) {
                    return $(elem).parent().addClass("site-grey");
                }
                if ($(elem).val() == 2) {
                    return $(elem).parent().addClass("site-yellow");
                }
                if ($(elem).val() == 3) {
                    return $(elem).parent().addClass("site-green");
                }
                if ($(elem).val() == 4) {
                    return $(elem).parent().addClass("site-red");
                }
            } else {
                return $(elem).parent().removeClass(function () {
                    return $(this).attr("class");
                });
            }
        }

        function setNoAccessChecked(elem) {
            switch (elem.value) {
                case "1":
                    return elem.checked = true;
                    break;
                case "2":
                    return elem.checked = false;
                    break;
                case "3":
                    return elem.checked = false;
                    break;
                case "4":
                    return elem.checked = false;
                    break;
            }
        }

        function clearInputBackground() {
            $('input').each(function () {
                $(this).prop('checked', false);
                return $(this).parent().removeClass(function () {
                    return $(this).attr("class");
                });
            });
        }

        function filterInputs(array) {

            // var testType = $("#available_tests").val();
            var testType = 0;
            if ($("#kiwifruit_tests").attr("hidden") == "hidden") {
                testType = $("#available_avo_tests").val();
            }
            if ($("#avo_tests").attr("hidden") == "hidden") {
                testType = $("#available_kiwifruit_tests").val();
            }

            clearInputBackground();
            array.forEach(function (item, index) {
                assignmentHistory(item, testType);
                var inputElements = $("input[name='" + item.users_id + "']").filter(function (index) {
                    return $(this).val() == item.groups_id && item.test_id == testType;
                }).each(function () {
                    $(this).prop('checked', true);
                    setClass($(this))
                });
            });
        }

        function assignmentHistory(obj, testType) {
            if (obj.test_id == testType) {
                $("[data=" + obj.users_id + "][id='assignedDate']").text(obj.updated_at);
                if(obj.updated_by != null){
                    $("[data=" + obj.users_id + "][id='assignedBy']").text(obj.updated_by);
                }else{
                    $("[data=" + obj.users_id + "][id='assignedBy']").text(" ");
                }
            }
        }

        $(document).ready(function () {
            var userGroups = $.get("ug", function () {
                filterInputs(userGroups.responseJSON);
            });

            if ($("#available_crops").val() == "Avocado") {
                $("#kiwifruit_tests").attr("hidden", true);
                $("#available_kiwifruit_tests").attr("disabled", true);

                $("#avo_tests").attr("hidden", false);
                $("#available_avo_tests").attr("disabled", false);
            }

            if ($("#available_crops").val() == "Kiwifruit") {
                $("#avo_tests").attr("hidden", true);
                $("#available_avo_tests").attr("disabled", true);

                $("#kiwifruit_tests").attr("hidden", false);
                $("#available_kiwifruit_tests").attr("disabled", false);
            }


            $("#available_kiwifruit_tests").select2()
                    .change(function () {
                        if ($.isNumeric($("#available_kiwifruit_tests").val())) {
                            filterInputs(userGroups.responseJSON);
                        }
                    });
            $("#available_avo_tests").select2()
                    .change(function () {
                        if ($.isNumeric($("#available_avo_tests").val())) {
                            filterInputs(userGroups.responseJSON);
                        }
                    });
            $("#available_crops").select2()
                    .change(function () {
                        if ($("#available_crops").val() == "Avocado") {

                            $("#kiwifruit_tests").attr("hidden", true);
                            $("#available_kiwifruit_tests").attr("disabled", true);

                            $("#avo_tests").attr("hidden", false);
                            $("#available_avo_tests").attr("disabled", false);

                            filterInputs(userGroups.responseJSON);
                        }
                        if ($("#available_crops").val() == "Kiwifruit") {

                            $("#avo_tests").attr("hidden", true);
                            $("#available_avo_tests").attr("disabled", true);

                            $("#kiwifruit_tests").attr("hidden", false);
                            $("#available_kiwifruit_tests").attr("disabled", false);

                            filterInputs(userGroups.responseJSON);
                        }
                    });

            $("#save_btn").click(function (event) {
                if (!confirm("Are you sure you want to save test groups?")) {
                    event.preventDefault();
                }
            });

            $("#reset_all").click(function () {
                if (confirm("Are you sure you want to reset all to NO ACCESS for this test?")) {
                    $("td input").each(function () {
                        setNoAccessChecked(this);
                        setClass(this);
                    });
                }else{
                    event.preventDefault();
                }
            });

            $("td input").change(function () {
                var $this = $(this);
                var td = $this.parent();
                td.siblings().filter(function () {
                    return !!$(this).find('input[name="' + $this.attr('name') + '"]:radio').length;
                }).removeClass(function () {
                    return $(this).attr("class");
                });
                setClass(this);
            });

        });

    </script>
@stop