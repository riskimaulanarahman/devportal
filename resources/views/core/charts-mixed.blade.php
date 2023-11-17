@extends('layouts.master')
@section('title') @lang('translation.Mixed_Chart') @endsection
@section('content')


@section('pagetitle')Mixed Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Line & Column Charts</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_column_chart" data-colors='["#3b76e1", "#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Multiple Y-Axis Charts</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multi_chart" data-colors='["#3b76e1", "#57c9eb"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Line & Area Charts</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_area_chart" data-colors='["#3b76e1", "#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Line, Column & Area Charts</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_area_charts" data-colors='["#3b76e1", "#63ad6f", "#f56e6e"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-mixed.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
