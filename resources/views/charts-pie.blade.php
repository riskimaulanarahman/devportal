@extends('layouts.master')
@section('title') @lang('translation.Pie_Charts') @endsection
@section('content')


@section('pagetitle')Pie Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Simple Pie Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="simple_pie_chart" data-colors='["#3b76e1", "#63ad6f","#f9c256", "#f56e6e","#564ab1"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Simple Donut Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="simple_dount_chart" data-colors='["#3b76e1", "#63ad6f","#f9c256", "#f56e6e","#564ab1"]'
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
                    <h4 class="card-title">Updating Donut Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="updating_donut_chart" data-colors='["#3b76e1", "#63ad6f","#f9c256", "#f56e6e"]'
                        class="apex-charts" dir="ltr"></div>

                    <div class="d-flex align-items-start flex-wrap gap-2 justify-content-center mt-4">
                        <button id="add" class="btn btn-light btn-sm">
                            + ADD
                        </button>

                        <button id="remove" class="btn btn-light btn-sm">
                            - REMOVE
                        </button>

                        <button id="randomize" class="btn btn-light btn-sm">
                            RANDOMIZE
                        </button>

                        <button id="reset" class="btn btn-light btn-sm">
                            RESET
                        </button>
                    </div>
                </div>

            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Monochrome Pie Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="monochrome_pie_chart" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Gradient Donut Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="gradient_chart" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e","#564ab1"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Patterned Donut Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="pattern_chart" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e","#564ab1"]'
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
                    <h4 class="card-title">Pie Chart with Image Fill</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="image_pie_chart" data-colors='["#3b76e1","#63ad6f","#f9c256","#f56e6e"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->

    @endsection
    @section('script')
        <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

        <script src="{{ URL::asset('assets/js/pages/apexcharts-pie.init.js') }}"></script>
        <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
    @endsection
