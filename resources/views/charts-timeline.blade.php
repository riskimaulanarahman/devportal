@extends('layouts.master')
@section('title') @lang('translation.Timeline_Charts') @endsection
@section('content')


@section('pagetitle')Timeline Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic TimeLine Charts</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_timeline" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Different Color For Each Bar</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="color_timeline" data-colors='["#3b76e1","#63ad6f", "#f9c256", "#f56e6e", "#564ab1"]' dir="ltr">
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
                    <h4 class="card-title">Multi Series Timeline</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multi_series" data-colors='["#3b76e1","#63ad6f"]' dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Advanced Timeline (Multiple Range)</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="advanced_timeline" data-colors='["#3b76e1","#63ad6f","#f9c256"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->


@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/moment/moment.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-timeline.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
