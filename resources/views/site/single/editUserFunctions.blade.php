@extends('app')

@section('content')
    <!--We have a user model here-->

    <h1>Functions Access - {{$user['firstName']}} {{$user['lastName']}}</h1>
    <br/>
    <!-- if there are login errors, show them here -->
    <div>
        @include('flash::message')
    </div>
    {!! Form::open(array('action' => 'PHPSite\UsersController@storeFunctions')) !!}
    {!! Form::text('id',$user['id'] ,array('hidden' => 'hidden' )) !!}
    <div class="container">
        <div class="row">

            <table class="table">
                <thead>
                <td></td>
                <td></td>
               <td><strong>Date/Time Assigned</strong></td>
                <td><strong>Assigned By</strong></td>

                </thead>
                <tbody>
                @foreach($availGroups as $group)
                    @if($group['group'] != "Sampler")

                    <?php $filled = false; ?>
                    <tr>
                        <td>
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
                        <td>
                            {!! Form::checkbox( $group['group'], $group['id'], $checked,['class' => 'check site-inputcheckbox']) !!}
                        </td>

                        @foreach($fullUserGroups as $ug)
                            @if($ug['groups_id'] == $group['id'])

                                <td>
                                    {{$ug['updated_at']}}
                                </td>
                                <td>
                                    {{$ug['updated_by']}}
                                </td>
                                <?php $filled = true;?>
                            @endif

                        @endforeach
                        @if($filled === false)
                        <td>Unassigned</td>
                            <td>Unassigned</td>
                        @endif
                    </tr>
                    @endif
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