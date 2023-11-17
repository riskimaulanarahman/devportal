@extends('layouts.master')
@section('title') @lang('translation.Timeline')  @endsection
@section('content')


@slot('title') Project Overview @endslot

@endsection
@section('script')
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
