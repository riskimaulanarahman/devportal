@extends('layouts.master')
@section('title') @lang('translation.Dashboards') @endsection
@section('content')
@section('pagetitle') Dashboard Project @endsection

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@</h4>
            </div>
            <div class="card-body">

                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-justified" role="tablist">
                    <li class="nav-item waves-effect waves-light">
                        <a class="nav-link active" data-bs-toggle="tab" href="#progress" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block">Progress</span>
                        </a>
                    </li>
                    <li class="nav-item waves-effect waves-light">
                        <a class="nav-link" data-bs-toggle="tab" href="#waiting" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block">Waiting</span>
                        </a>
                    </li>
                    <li class="nav-item waves-effect waves-light">
                        <a class="nav-link" data-bs-toggle="tab" href="#completed" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                            <span class="d-none d-sm-block">Completed</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="progress" role="tabpanel">
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card" style="height: 450px">
                                        <div class="table-responsive">
                                            <table class="table project-list-table table-nowrap align-middle table-borderless table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 100px" class="ps-4">#</th>
                                                        <th scope="col">Projects</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Team</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach($project['progress'] as $item)
                                                    <tr>
                                                        <td class="ps-4">{{ $no++ }}</td>
                                                        <td>
                                                            <h5 class="text-truncate font-size-14"><a href="javascript: void(0);" class="text-dark">{{ $item['nameSystem'] }}</a></h5>
                                                            <p class="text-muted mb-0">Request by : {{ $item['fullname'] }}</p>
                                                        </td>
                                                        <td class="pe-5">
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <div class="progress" style="height: 6px;">
                                                                        <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $item['progress'] }}%" aria-valuenow="{{ $item['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <h6 class="mb-0 font-size-13"> {{ $item['progress'] }}%</h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-group">
                                                                @foreach($item['initials'] as $initial)
                                                                <div class="avatar-group-item">
                                                                    <a href="javascript: void(0);" class="d-inline-block">
                                                                        <div class="avatar-sm">
                                                                            <span class="avatar-title rounded-circle text-white font-size-16" style="background-color: orange">
                                                                                {{ $initial }}
                                                                            </span>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="waiting" role="tabpanel">
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card" style="height: 450px">
                                        <div class="table-responsive">
                                            <table class="table project-list-table table-nowrap align-middle table-borderless table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 100px" class="ps-4">#</th>
                                                        <th scope="col">Projects</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Team</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach($project['waiting'] as $item)
                                                    <tr>
                                                        <td class="ps-4">{{ $no++ }}</td>
                                                        <td>
                                                            <h5 class="text-truncate font-size-14"><a href="javascript: void(0);" class="text-dark">{{ $item['nameSystem'] }}</a></h5>
                                                            <p class="text-muted mb-0">Request by : {{ $item['fullname'] }}</p>
                                                        </td>
                                                        <td class="pe-5">
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <div class="progress" style="height: 6px;">
                                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $item['progress'] }}%" aria-valuenow="{{ $item['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <h6 class="mb-0 font-size-13"> {{ $item['progress'] }}%</h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-group">
                                                                @foreach($item['initials'] as $initial)
                                                                <div class="avatar-group-item">
                                                                    <a href="javascript: void(0);" class="d-inline-block">
                                                                        <div class="avatar-sm">
                                                                            @if ($initial == null)
                                                                                <span class="avatar-title rounded-circle bg-danger text-white font-size-16">
                                                                                    -
                                                                                </span>
                                                                            @else
                                                                                <span class="avatar-title rounded-circle bg-primary text-white font-size-16">
                                                                                    {{ $initial }}
                                                                                </span> 
                                                                            @endif
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="completed" role="tabpanel">
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card" style="height: 450px">
                                        <div class="table-responsive">
                                            <table class="table project-list-table table-nowrap align-middle table-borderless table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 100px" class="ps-4">#</th>
                                                        <th scope="col">Projects</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Team</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $no = 1;
                                                    @endphp
                                                    @foreach($project['completed'] as $item)
                                                    <tr>
                                                        <td class="ps-4">{{ $no++ }}</td>
                                                        <td>
                                                            <h5 class="text-truncate font-size-14"><a href="javascript: void(0);" class="text-dark">{{ $item['nameSystem'] }}</a></h5>
                                                            <p class="text-muted mb-0">Request by : {{ $item['fullname'] }}</p>
                                                        </td>
                                                        <td class="pe-5">
                                                            <div class="row align-items-center">
                                                                <div class="col">
                                                                    <div class="progress" style="height: 6px;">
                                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item['progress'] }}%" aria-valuenow="{{ $item['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <h6 class="mb-0 font-size-13"> {{ $item['progress'] }}%</h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-group">
                                                                @foreach($item['initials'] as $initial)
                                                                <div class="avatar-group-item">
                                                                    <a href="javascript: void(0);" class="d-inline-block">
                                                                        <div class="avatar-sm">
                                                                            <span class="avatar-title rounded-circle bg-success text-white font-size-16">
                                                                                {{ $initial }}
                                                                            </span>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<!-- apexcharts -->
{{-- <script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script> --}}

<!-- dashboard init -->
{{-- <script src="{{ URL::asset('/assets/js/pages/dashboard.init.js') }}"></script> --}}
{{-- <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script> --}}
@endsection
