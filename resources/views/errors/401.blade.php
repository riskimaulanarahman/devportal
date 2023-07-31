@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.Error_404')
@endsection
@section('body')

    <body class="bg-light">
    @endsection
@section('content')
    <section class="authentication-bg min-vh-100 py-5 py-lg-0">
        <div class="bg-overlay bg-light"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center mb-5">
                                <h1 class="error-title"> <span class="blink-infinite">401</span></h1>
                                <h4 class="text-uppercase">Unauthorized</h4>
                                <p class="font-size-15 mx-auto text-muted w-50 mt-4">You does not have permissions for the requested operation</p>
                                <div class="mt-5 text-center">
                                    <a class="btn btn-primary waves-effect waves-light" href="{{ url('/') }}">Back to
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
        </div>
    </section>

@endsection
