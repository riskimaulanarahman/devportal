@extends('errors::minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Page Expired'))
<a class="" href="{{ url('/') }}">Back to Home</a>
