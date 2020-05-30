@extends('app')

@section('content')
        <!--We have a user model here-->
{!! Form::open(array('action' => 'PHPSite\UsersController@storeStaffAccess')) !!}
{!! Form::text('id',$user['id'] ,array('hidden' => 'hidden' )) !!}
        <!-- if there are login errors, show them here -->
<div>
    @include('flash::message')
</div>

<div class="row">
    <div class="col-sm-8">
        <h1>Staff Access - {{$user['firstName']}} {{$user['lastName']}}</h1>
        <a onclick="window.location='{{ url("/site/editUsers/access") }}'">- Test Access</a>
    </div>

    <div class="col-sm-4" style=" padding-top: 25px;">
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
    </div>
</div>
<br>
<br/>
<div class="container">
    <div class="row">
        <!-- Date Range Picker
        {!! Form::label('errors', 'Error Date Range',array('style'=>'bold')) !!}
                <div class="input-daterange input-group" id="datepicker">
                    {!! Form::text('from',null,array('placeholder' => 'from','name'=>'start')) !!}
                &nbsp;&nbsp;&nbsp;
            {!! Form::text('to',null,array('placeholder' => 'to','name'=>'end')) !!}
                </div>
                Showing From <span id="fromDate"></span> To <span id="toDate"></span>
                        -->
        <table class="table">
            <thead>
            <td></td>
            <td><strong>Date/Time Assigned</strong></td>
            <td><strong>Assigned By</strong></td>
            <td>
                <span style="color:red;"><strong>ERRORS</strong></span>
            </td>
            @foreach($availGroups as $group)

                @if($group['id'] < 5 )
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
                        <span style="background-color: #FFFFFF"><strong> {{$group['group']}} </strong></span>
                    </td>
                @endif

            @endforeach
            </thead>
            <tbody>
            @foreach($testTypes as $tt)

                <tr id= <?php $crop_id = explode(':', $tt['name']); if(array_key_exists(2,$crop_id)){ echo $crop_id[2];}else{echo "Kiwifruit";}?> >
                    <td>
                        <?php $crop_id = explode(':', $tt['name']);
                        if(array_key_exists(3,$crop_id)){
                            switch ($crop_id[3]) {
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
                                    echo $crop_id[3];
                                    break;
                            }
                        }else{
                            echo "DM Approval";
                        }?>
                    </td>

                   @foreach($fullUserGroups as $ug)
                        @if($ug['test_id'] == $tt['id'])
                        <td>
                            {{$ug['updated_at']}}
                        </td>
                            <td>
                              {{$ug['updated_by']}}
                            </td>
                        @endif
                    @endforeach

                    <td>
                        <?php $i = 0?>
                        @foreach($errors as $error)
                            @if($error["appliance_test"] != null && $error["appliance_test"]->name == $tt['name'])
                                <?php $i++ ?>
                            @endif
                        @endforeach
                        @if($i != 0)
                            <a>{{$i}} Errors</a>
                        @endif
                    </td>
                    @foreach($availGroups as $group)
                        @if($group['id'] < 5)
                            <?php
                            foreach ($fullUserGroups as $ug) {
                                $checked = false;
                                if ($ug['test_id'] == $tt['id'] && $ug['groups_id'] == $group['id']) {
                                    $checked = true;
                                    break;
                                }
                            }
                            ?>
                            <td>
                                {!! Form::radio( $tt['id'], $group['id'], $checked,['class' => 'check']) !!}
                            </td>
                        @endif
                    @endforeach
                </tr>

            @endforeach
            </tbody>
        </table>
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
            if (elem.checked) {
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

        function setRowByCrop(elem) {
            if (elem.id != null && elem.id != "") {
                if (elem.id != $('#available_crops').val()) {
                    $(elem).attr('hidden', true);
                } else {

                    $(elem).attr('hidden', false);

                }
            }
        }

        $(document).ready(function () {
            var u = "<?php echo $user['id'] ?>";
            var td = $("td input");
            var tr = $('.table tr');
            var from = $('.datepicker').datepicker('getStartDate');

            $("#datepicker").datepicker({
                format: "dd/mm/yy",
                todayHighlight: true,
                autoclose: true
            }).on("changeDate", function () {
                $('#fromDate').text($("#fromDate").val()); //
            });

            $("#save_btn").click(function (event) {
                if (!confirm("Are you sure you want to save access?")) {
                    event.preventDefault();
                }
            });

            $("#available_crops").select2()
                    .change(function () {
                        if ($("#available_crops").val() == "Avocado") {
                            tr.each(function (elem) {
                                setRowByCrop(this);
                            });

                        }
                        if ($("#available_crops").val() == "Kiwifruit") {
                            tr.each(function (elem) {
                                setRowByCrop(this);
                            });
                        }
                    });


            $("#reset_all").click(function () {
                td.each(function (elem) {
                    setNoAccessChecked(this);
                    setClass(this);
                });
            });

            td.change(function () {
                var $this = $(this);
                var td = $this.parent();
                td.siblings().filter(function () {
                    return !!$(this).find('input[name="' + $this.attr('name') + '"]:radio').length;
                }).removeClass(function () {
                    return $(this).attr("class");
                });
                setClass(this);
            });
            tr.each(function (elem) {
                setRowByCrop(this);
            });
            td.each(function (elem) {
                setClass(this);
            });


        });

    </script>
@stop