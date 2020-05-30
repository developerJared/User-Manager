@extends('app')

@section('content')
        <!--We have a user model here-->

<h1>Cutter Access - {{$user['firstName']}} {{$user['lastName']}}</h1>
<br/>
<!-- if there are login errors, show them here -->
<div>
    @include('flash::message')
</div>
{!! Form::open(array('action' => 'PHPSite\UsersController@storeCutters')) !!}
{!! Form::text('id',$user['id'] ,array('hidden' => 'hidden' )) !!}
<div class="container">
    <div class="row">

        <table class="table">
            <tbody>
            @foreach($availGroups as $group)
                <tr>
                    <td class="col-sm-6">
                       <strong>{{$group['group']}}</strong>
                    </td>

                        <?php
                            foreach ($fullUserGroups as $ug) {
                                $checked = false;
                                if ($ug['test_id'] == 0 && $ug['groups_id'] == $group['id']) {
                                    $checked = true;
                                    break;
                                }
                            }
                        ?>
                    <td class="col-sm-6">
                        {!! Form::checkbox( $group['group'], $group['id'], $checked,['class' => 'check site-inputcheckbox']) !!}
                    </td>
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
        $(document).ready(function () {
            var td = $("td input");

            $("#save_btn").click(function (event) {
                if (!confirm("Are you sure you want to save?")) {
                    event.preventDefault();
                }
            });
        });
    </script>
@stop