@extends('layouts.master')
@section('title') @lang('translation.Scatter_Charts') @endsection
@section('content')


@section('pagetitle')Scatter Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Scatter Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_scatter" data-colors='["#3b76e1", "#63ad6f","#f9c256"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Scatter - Datetime Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="datetime_scatter" data-colors='["#3b76e1", "#63ad6f","#f9c256","#e83e8c","#564ab1"]'
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
                    <h4 class="card-title">Scatter Images Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="images_scatter" data-colors='["#564ab1", "#f56e6e"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-scatter.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
