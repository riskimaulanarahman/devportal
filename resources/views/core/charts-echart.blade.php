@extends('layouts.master')
@section('title') @lang('translation.E_Charts') @endsection
@section('content')


@section('pagetitle')Echarts @endsection


    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Line Chart</h4>
                </div>
                <div class="card-body">
                    <div id="line-chart" data-colors='["#63ad6f"]' class="e-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Mix Line-Bar</h4>
                </div>
                <div class="card-body">
                    <div id="mix-line-bar" data-colors='["#63ad6f", "#3b76e1", "#f56e6e"]' class="e-charts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Doughnut Chart</h4>
                </div>
                <div class="card-body">
                    <div id="doughnut-chart" data-colors='["#3b76e1", "#f9c256", "#f56e6e", "#57c9eb", "#63ad6f"]'
                        class="e-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Pie Chart</h4>
                </div>
                <div class="card-body">
                    <div id="pie-chart" data-colors='["#f56e6e", "#63ad6f", "#57c9eb", "#f9c256", "#3b76e1"]'
                        class="e-charts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Scatter Chart</h4>
                </div>
                <div class="card-body">
                    <div id="scatter-chart" data-colors='["#63ad6f"]' class="e-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bubble Chart</h4>
                </div>
                <div class="card-body">
                    <div id="bubble-chart" class="e-charts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Candlestick Chart</h4>
                </div>
                <div class="card-body">
                    <div id="candlestick-chart" data-colors='["#3b76e1","#63ad6f"]' class="e-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Gauge Chart</h4>
                </div>
                <div class="card-body">
                    <div id="gauge-chart" data-colors='["#63ad6f","#3b76e1","#f56e6e"]' class="e-charts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/echarts/echarts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/echarts.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
