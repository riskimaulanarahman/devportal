@extends('layouts.master')
@section('title') @lang('translation.Radialbar_Charts') @endsection
@section('content')


@section('pagetitle')Radialbar Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Radialbar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_radialbar" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Multiple Radialbar</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multiple_radialbar" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e"]'
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
                    <h4 class="card-title">Circle Chart - Custom Angle</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="circle_radialbar" data-colors='["#3b76e1","#57c9eb","#f1734f","#63ad6f"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Gradient Circle Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="gradient_radialbar" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row pb-4">
        <div class="col-lg-6">
            <div class="card h-100 mb-xl-0">
                <div class="card-header">
                    <h4 class="card-title">Stroked Circular Gauge</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="stroked_radialbar" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card h-100 mb-xl-0">
                <div class="card-header">
                    <h4 class="card-title">Semi Circular Gauge</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="semi_radialbar" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-radialbar.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
