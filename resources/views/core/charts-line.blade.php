@extends('layouts.master')
@section('title') @lang('translation.Line_Charts') @endsection
@section('content')


@section('pagetitle')Line Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Line Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_basic" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Zoomable Timeseries</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_zoomable" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title">Line with Data Labels</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_datalabel" data-colors='["#57c9eb","#f56e6e"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Dashed Line</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_dashed" data-colors='["#57c9eb","#f56e6e","#63ad6f"]' class="apex-charts"
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
                    <h4 class="card-title">Line with Annotations</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_annotations" data-colors='["#57c9eb"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Syncing Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="apex-charts" dir="ltr">
                        <div id="syncingchart-line" data-colors='["#3b76e1"]'></div>
                        <div id="syncingchart-area" data-colors='["#63ad6f"]'></div>
                    </div>
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
                    <h4 class="card-title">Brush Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div>
                        <div id="brushchart_line2" data-colors='["#f56e6e"]' class="apex-charts" dir="ltr"></div>
                        <div id="brushchart_line" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Stepline Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_stepline" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
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
                    <h4 class="card-title">Gradient Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_gradient" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Missing Data/ Null Value Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_missing_data" data-colors='["#3b76e1","#f56e6e","#63ad6f"]' class="apex-charts"
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
                    <h4 class="card-title">Realtimes Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="line_chart_realtime" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-line.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
