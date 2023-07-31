@extends('layouts.master')
@section('title') @lang('translation.Column_Chart') @endsection
@section('content')

@section('pagetitle')Column Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Column Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_chart" data-colors='["#f56e6e", "#3b76e1", "#63ad6f"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Column with Data Labels</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_chart_datalabel" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title">Stacked Column Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_stacked" data-colors='["#3b76e1", "#63ad6f", "#f9c256","#f56e6e"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Stacked Column 100</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_stacked_chart" data-colors='["#3b76e1", "#63ad6f", "#f9c256"]' class="apex-charts"
                        dir="ltr"></div>
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
                    <h4 class="card-title">Column with Markers</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_markers" data-colors='["#63ad6f", "#564ab1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Column with Rotated Labels</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_rotated_labels" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title">Column with Nagetive Values</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_nagetive_values" data-colors='["#3b76e1","#f1734f","#f9c256"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Range Column Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_range" data-colors='["#3b76e1","#63ad6f"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title">Dynamic Loaded Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="dynamicloadedchart-wrap">
                        <div id="chart-year" class="apex-charts"></div>
                        <div id="chart-quarter" class="apex-charts"></div>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Distributed Columns Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="column_distributed" data-colors='["#3b76e1","#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/apexcharts-column.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
