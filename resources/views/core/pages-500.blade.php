@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.Error_500')
@endsection
@section('body')

    <body class="bg-light">
    <section class="authentication-bg min-vh-100 py-5 py-lg-0">
        <div class="bg-overlay bg-light"></div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="text-center mb-5">
                                    <h1 class="error-title"> <span class="blink-infinite">500</span></h1>
                                    <h4 class="text-uppercase">Internal Server Error</h4>
                                    <p class="font-size-15 mx-auto text-muted w-50 mt-4">It will be as simple as Occidental
                                        in fact, it will Occidental to an English person</p>
                                    <div class="mt-5 text-center">
                                        <a class="btn btn-primary waves-effect waves-light" href="index">Back to
                                            Dashboard</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-xl-7">
                                <div class="mt-2">
                                    <img src="{{ URL::asset('assets/images/error-img.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
