@extends('layouts.master')
@section('title') @lang('translation.Polararea_Charts') @endsection
@section('content')


@section('pagetitle')Polararea Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic-Polar Area Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_polar_area"
                        data-colors='["#3b76e1", "#51d28c", "#f7cc53", "#f34e4e", "#564ab1", "#5fd0f3"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Polar-Area Monochrome</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="monochrome_polar_area" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-polararea.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
