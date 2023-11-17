@extends('layouts.master')
@section('title') @lang('translation.Heatmap_Charts') @endsection
@section('content')


@section('pagetitle')Heatmap Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Heatmap Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_heatmap" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Heatmap - Multiple Series</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multiple_heatmap"
                        data-colors='["#3b76e1", "#e83e8c", "#f7cc53", "#564ab1", "#51d28c", "#f1734f", "#5fd0f3","#f34e4e"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Heatmap Color Range</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="color_heatmap" data-colors='["#3b76e1","#63ad6f", "#564ab1", "#f9c256"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Heatmap - Range Without Shades</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="shades_heatmap" data-colors='["#3b76e1","#57c9eb"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/apexcharts-heatmap.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
