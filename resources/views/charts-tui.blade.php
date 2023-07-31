@extends('layouts.master')
@section('title') @lang('translation.Toast_UI_Charts') @endsection
@section('css')
    <link href="{{ URL::asset('assets/libs/tui-chart/tui-chart.min.css') }}" rel="stylesheet">
@endsection
@section('content')


@section('pagetitle')Toast UI Charts @endsection


    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bar charts</h4>
                </div>
                <div class="card-body">
                    <div id="bar-charts" data-colors='["#3b76e1", "#63ad6f"]' dir="ltr"></div>

                </div>
            </div>
        </div> <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Column charts</h4>
                </div>
                <div class="card-body">
                    <div id="column-charts" data-colors='["#63ad6f", "#3b76e1","#f56e6e"]' dir="ltr"></div>

                </div>
            </div>
        </div> <!-- end col -->

    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Line charts</h4>
                </div>
                <div class="card-body">
                    <div id="line-charts" data-colors='["#63ad6f", "#3b76e1","#f56e6e"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Area charts</h4>
                </div>
                <div class="card-body">
                    <div id="area-charts" data-colors='["#f56e6e","#63ad6f","#3b76e1"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->

    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bubble charts</h4>
                </div>
                <div class="card-body">
                    <div id="bubble-charts" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e","#57c9eb"]' dir="ltr">
                    </div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Scatter charts</h4>
                </div>
                <div class="card-body">
                    <div id="scatter-charts" data-colors='["#63ad6f","#3b76e1"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pie charts</h4>
                </div>
                <div class="card-body">
                    <div id="pie-charts" data-colors='["#3b76e1","#63ad6f", "#57c9eb","#f56e6e","#f9c256"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Donut pie charts</h4>
                </div>
                <div class="card-body">
                    <div id="donut-charts" data-colors='["#3b76e1","#63ad6f", "#57c9eb"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Heatmap charts</h4>
                </div>
                <div class="card-body">
                    <div id="heatmap-charts" data-colors='["#63ad6f","#3b76e1","#f56e6e"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Treemap charts</h4>
                </div>
                <div class="card-body">
                    <div id="treemap-charts" data-colors='["#3b76e1","#63ad6f","#f9c256", "#f56e6e"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->

    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Map charts</h4>
                </div>
                <div class="card-body">
                    <div id="map-charts" data-colors='["#3b76e1","#63ad6f"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Boxplot charts</h4>
                </div>
                <div class="card-body">
                    <div id="boxplot-charts" data-colors='["#3b76e1","#63ad6f"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->

    </div>
    <!-- end row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bullet charts</h4>
                </div>
                <div class="card-body">
                    <div id="bullet-charts" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e"]' dir="ltr"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Radial charts</h4>
                </div>
                <div class="card-body">
                    <div id="radial-charts" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e"]' dir="ltr"></div>
                </div>
            </div>
        </div> <!-- end col -->
    </div>

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/tui-chart/tui-chart.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/tui-charts.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
