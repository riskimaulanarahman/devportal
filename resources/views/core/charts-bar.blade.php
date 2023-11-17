@extends('layouts.master')
@section('title') @lang('translation.Bar_Charts') @endsection
@section('content')



@section('pagetitle')Bar Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Bar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="bar_chart" data-colors='["#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Custom DataLabels Bar</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="custom_datalabels_bar" data-colors='["#57c9eb", "#495057", "#e83e8c", "#63ad6f", "#f56e6e", "#2b908f", "#f9a3a4", "#564ab1",
                                            "#f1734f", "#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Stacked Bar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="stacked_bar" data-colors='["#3b76e1", "#63ad6f", "#f9c256", "#e83e8c", "#564ab1"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Stacked Bars 100</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="stacked_bar_100" data-colors='["#3b76e1", "#63ad6f", "#f9c256", "#f56e6e", "#564ab1"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bar with Negative Values</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="negative_bars" data-colors='["#f56e6e", "#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bar with Markers</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="bar_markers" data-colors='["#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Reversed Bar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="reversed_bars" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Patterned Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="patterned_bars" data-colors='["#3b76e1", "#63ad6f", "#f9c256", "#f56e6e"]'
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
                    <h4 class="card-title">Grouped Bar Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="grouped_bar" data-colors='["#3b76e1", "#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bar with Images</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="bar_images" class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/apexcharts-bar.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
