@extends('layouts.master')
@section('title') @lang('translation.Radar_Charts') @endsection
@section('content')


@section('pagetitle')Radar Charts @endsection



    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Radar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_radar" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Radar Chart - Multiple series</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multi_radar" data-colors='["#3b76e1","#57c9eb","#63ad6f"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Radar Chart - Polygon Fill</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="polygon_radar" data-colors='["#57c9eb"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-radar.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
