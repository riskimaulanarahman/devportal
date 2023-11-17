@extends('layouts.master')
@section('title') @lang('translation.Range_Slider') @endsection
@section('css')
    <link href="{{ URL::asset('assets/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
@endsection
@section('content')


        @section('pagetitle')Range Slider @endsection


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header justify-content-between d-flex align-items-center">
                    <h4 class="card-title">Examples</h4>
                    <a href="https://refreshless.com/nouislider/examples/" target="_blank"
                        class="btn btn-sm btn-soft-secondary">Docs <i class="mdi mdi-arrow-right align-middle"></i></a>
                </div><!-- end card header -->
                <div class="card-body">
                    <div>
                        <div class="mb-5">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Basic Example</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div id="slider"></div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Colorpicker</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div class="sliders" id="red"></div>
                                    <div class="sliders" id="green"></div>
                                    <div class="sliders" id="blue"></div>

                                    <div id="result"></div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Using HTML5 Input Elements</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div id="html5"></div>
                                    <div class="d-flex flex-wrap">
                                        <select id="input-select" class="form-select shadow-none"></select>
                                        <input type="number" class="form-control shadow-none" min="-20" max="40" step="1"
                                            id="input-number">
                                    </div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Non linear slider</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div id="nonlinear"></div>
                                    <span class="example-val" id="lower-value"></span>
                                    <span class="example-val" id="upper-value"></span>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Locking sliders together</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div class="slider" id="slider1"></div>
                                    <span class="example-val mt-2" id="slider1-span"></span>

                                    <div class="slider" id="slider2"></div>
                                    <span class="example-val mt-2" id="slider2-span"></span>

                                    <button id="lockbutton" class="btn btn-primary"
                                        style="float: right; margin: 20px 0 0;">Lock</button>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Only showing tooltips when sliding handle</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div class="slider" id="slider-hide"></div>
                                </div><!-- end col -->
                            </div><!-- end row-->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Colored Connect Elements</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div class="slider" id="slider-color"></div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Creating a toggle</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div id="slider-toggle"></div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>

                        <div class="mb-5">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h5 class="font-size-14">Soft limits</h5>
                                </div><!-- end col -->
                                <div class="col-lg-9">
                                    <div id="soft"></div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/wnumb/wnumb.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/range-sliders.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
