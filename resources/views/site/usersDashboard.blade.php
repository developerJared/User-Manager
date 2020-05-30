@extends('app')
@section('navbar')
    @parent

@stop

@section('content')

    <div class="container-fluid">

        <div class="row">
            <div class="panel panel-info">
                <div class="panel-heading" style="text-align: center;">
                    <span style="font-size: 1.7em;"><strong>Single User</strong></span>
                </div>
                <div class="panel-content" style="align-content: center;">
                    <!--- <button type="button" class="btn btn-primary centered-dashboard-button"
                            onclick="window.location='{{ url("site/editUsers/info") }}'"><span style="font-size: x-large">Edit User</span>
                    </button> --->
                    <button type="button" class="btn btn-primary centered-dashboard-button"
                            onclick="window.location='{{ url("site/editUsers/access") }}'"><span style="font-size: x-large">Single User Access</span>
                    </button>
                    <!-- Had internal constraints here
                        <button type="button" class="btn btn-primary centered-dashboard-button"
                                onclick="window.location='{{ url("site/addUser") }}'"><span style="font-size: x-large">Add User</span>
                        </button>
                    -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="panel panel-info">
                <div class="panel-heading" style="text-align: center;">
                    <span style="font-size: 1.7em;"><strong>Tests & Functions</strong></span>
                </div>
                <div class="panel-content">
                    <button type="button" class="btn btn-primary centered-dashboard-button"
                            onclick="window.location='{{ url("site/testAccess") }}'">
                        <span style="font-size: x-large">Test Access</span>
                    </button>

                    <button type="button" class="btn btn-primary centered-dashboard-button"
                            onclick="window.location='{{ url("site/functionAccess") }}'">
                        <span style="font-size: x-large">Functions / Reports Access</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

@stop
@section('footer')
@stop