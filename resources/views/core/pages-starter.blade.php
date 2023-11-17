@extends('layouts.master')
@section('title') @lang('translation.Starter_Page')  @endsection
@section('content')


@section('pagetitle') Starter Page @endsection

@endsection
@section('script')
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
