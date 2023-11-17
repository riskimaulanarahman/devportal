@extends('layouts.master')
@section('title') @lang('translation.Shops') @endsection
@section('css')
    <link href="{{ URL::asset('assets/libs/gridjs/gridjs.min.css') }}" rel="stylesheet">

@endsection
@section('content')


        @slot('title')Shops @endslot


    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">

                    <div id="table-ecommerce-shops"></div>

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/gridjs/gridjs.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/ecommerce-shops.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
