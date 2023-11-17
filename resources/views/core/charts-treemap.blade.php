@extends('layouts.master')
@section('title') @lang('translation.Treemap_Charts') @endsection
@section('content')


@section('pagetitle')Treemap Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header justify-content-between d-flex align-items-center">
                    <h4 class="card-title">Basic Treemap Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_treemap" data-colors='["#3b76e1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header justify-content-between d-flex align-items-center">
                    <h4 class="card-title">Multi-Dimensional Treemap Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="multi_treemap" data-colors='["#3b76e1","#63ad6f"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header justify-content-between d-flex align-items-center">
                    <h4 class="card-title">Distributed Treemap Chart (Different Color for each Cell)</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="distributed_treemap" data-colors='["#3b76e1","#f9c256","#63ad6f","#e83e8c","#564ab1",
                                        "#51d28c","#5fd0f3","#f34e4e","#51d28c","#e83e8c","#495057","#f1734f"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header justify-content-between d-flex align-items-center">
                    <h4 class="card-title">Treemap Chart with Color Ranges</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="color_range_treemap" data-colors='["#63ad6f","#3b76e1"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ URL::asset('assets/js/pages/apexcharts-treemap.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
