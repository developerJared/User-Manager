@extends('app')
@section('navbar')
@parent
@stop

@section('content')

<div class="container-fluid">
    <p>
    <h3 style="text-align: center"><strong> Edit all user access for a particular test </strong></h3> </p>
    <button type="button" class="btn btn-primary" style="display: block; width: 100%;height:50px;"
            onclick="window.location='{{ url("site/testAccess") }}'">
    <span style="font-size: x-large">All Staff Test Access</span>
    </button>
    <br/>
    <br/>

    <!--<p>
    <h3 style="text-align: center"><strong> cutters </strong></h3> </p>
    <button type="button" class="btn btn-primary" style="display: block; width: 100%;height:50px;"
            onclick="window.location='{{ url("site/cutters") }}'"><span
        style="font-size: x-large">Access</span>
    </button>

    <p>
    <h3 style="text-align: center"><strong> functions </strong></h3> </p>
    <button type="button" class="btn btn-primary" style="display: block; width: 100%;height:50px;"
            onclick="window.location='{{ url("site/functions") }}'"><span
        style="font-size: x-large">Access</span>
    </button>-->


</div>
@stop


@section('footer')
@stop