@extends('layouts.master')
@section('title') @lang('translation.Candlestick_Charts') @endsection
@section('content')



@section('pagetitle') Candlestick Charts @endsection


                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Basic Candlestick Chart</h4>
                                     </div><!-- end card header -->
                                    <div class="card-body">
                                        <div id="basic_candlestick" data-colors='["#f56e6e", "#3b76e1"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div><!--end card-->
                            </div><!-- end col -->
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Candlestick Synced with Brush Chart (Combo)</h4>
                                     </div><!-- end card header -->
                                    <div class="card-body">
                                        <div id="combo_candlestick" data-colors='["#f56e6e", "#3b76e1"]' class="apex-charts" dir="ltr"></div>
                                        <div id="combo_candlestick_chart" data-colors='["#f56e6e", "#f9c256"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div><!--end card-->
                            </div><!-- end col -->
                        </div><!-- end row -->

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Category X-Axis</h4>
                                     </div><!-- end card header -->
                                    <div class="card-body">
                                        <div id="category_candlestick" data-colors='["#f56e6e", "#3b76e1"]' class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div><!--end card-->
                            </div><!-- end col -->

                        </div><!-- end row -->

@endsection
@section('script')
<script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="https://apexcharts.com/samples/assets/ohlc.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.8.17/dayjs.min.js"></script>

<script src="{{ URL::asset('assets/js/pages/apexcharts-candlestick.init.js') }}"></script>
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
