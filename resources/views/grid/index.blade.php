{{-- Layout --}}
@extends('layouts.master')

{{-- tab title --}}
@section('title')
    {{ $data['title'] }}
@endsection

{{-- page title --}}
@section('pagetitle')
    {{ $data['title'] }}
@endsection

{{-- content --}}
@section('content')
    @if ($data['menu'] != null)
        <div class="topnav">
            <div class="container-fluid">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">
                            @foreach ($data['menu'] as $key => $item)
                                @if ($data['menu'][$key]['submenu'] == null)
                                    <li class="nav-item">
                                        <a class="nav-link dropdown-toggle arrow-none" href="{{ $data['menu'][$key]['route'] }}"
                                            id="topnav-dashboard" role="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="icon nav-icon" data-eva="{{ $data['menu'][$key]['icon'] }}"></i>
                                            <span data-key="t-dashboards">{{ $data['menu'][$key]['title'] }}</span>
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-dashboard" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon nav-icon" data-eva="{{ $data['menu'][$key]['icon'] }}"></i>
                                            <span data-key="t-dashboards">{{ $data['menu'][$key]['title'] }}</span>
                                            <div class="arrow-down"></div>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
                                            @foreach ($data['menu'][$key]['submenu'] as $keysub => $itemsub)
                                            <a href="{{ $data['menu'][$key]['submenu'][$keysub]['route'] }}"
                                                class="dropdown-item"
                                                data-key="t-ecommerce">
                                                <i class="icon nav-icon" data-eva="{{ $data['menu'][$key]['submenu'][$keysub]['icon'] }}"></i>
                                                <span data-key="t-dashboards">{{ $data['menu'][$key]['submenu'][$keysub]['title'] }}</span>
                                            </a>
                                            @endforeach
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    @endif

    <div class="card {{ $data['menu'] != null ? 'mt-3' : null }}">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar">
                        <div class="avatar-title rounded bg-primary bg-gradient">
                            <i class="fill-white" data-eva="{{ $data['icon'] }}"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted mb-1">{{ $data['module'] }}</p>
                    <h4 class="mb-0">List {{ $data['title'] }}</h4>
                </div>
                @if ($data['module'] == 'SubmissionRequest')
                    <div class="flex-shrink-0 align-self-end ms-2">
                        <button class="btn btn-primary w-100 waves-effect waves-light" id="btnadd" type="button">Create new request</button>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card-body" style="padding: 10px; !important">
            <div id="gridContainer" style="height: 600px;"></div>
            <div id="popup"></div>
        </div>

    </div>
@endsection