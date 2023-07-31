@extends('layouts.master')
@section('title') @lang('translation.Boxplot_Charts') @endsection
@section('content')



@section('pagetitle')Boxplot Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Basic Box Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="basic_box" data-colors='["#3b76e1", "#57c9eb"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Boxplot with Scatter Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="box_plot" data-colors='["#f56e6e", "#564ab1"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/apexcharts-boxplot.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
