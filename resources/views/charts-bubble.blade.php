@extends('layouts.master')
@section('title') @lang('translation.Bubble_Charts') @endsection
@section('content')



@section('pagetitle')Bubble Charts @endsection


    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Simple Bubble Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="simple_bubble" data-colors='["#3b76e1", "#564ab1","#f9c256", "#63ad6f"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">3D Bubble Chart</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div id="bubble_chart" data-colors='["#3b76e1", "#564ab1","#f9c256", "#63ad6f"]' class="apex-charts"
                        dir="ltr"></div>
                </div>
            </div>
            <!--end card-->
        </div><!-- end col -->
    </div><!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/apexcharts-bubble.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
