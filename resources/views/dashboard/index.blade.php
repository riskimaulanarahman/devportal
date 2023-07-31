@extends('layouts.master')
@section('title') @lang('translation.Dashboards') @endsection
@section('content')
@section('pagetitle') {{ auth::user()->fullname }}, <small>Welcome to your Dashboard.</small> @endsection
<div class="row">
    <div class="col-xxl-9">
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-gradient">
                                        <i data-eva="clock" class="fill-white"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pending Submissions</p>
                                <h4 class="mb-0">{{ $totalPendingSubmission }}</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-gradient">
                                        <i data-eva="checkmark-square-outline" class="fill-white"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Need Your Approval</p>
                                <h4 class="mb-0">{{ $totalPendingApproval }}</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

        <div class="row">
      
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-3">List of Pending Submissions</h5>
                            </div>
                        </div>
                        @if ($totalPendingSubmission == 0)
                            There are no submissions being processed
                        @else
                        <div class="mx-n4" data-simplebar style="max-height: 296px;">
                            <ul class="list-unstyled mb-0">
                                @php
                                    $numeric = 1;
                                @endphp
                                @foreach ($listPendingSubmission as $tableName => $records)
                                    @foreach ($records as $index => $record)
                                        <li class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-primary bg-gradient rounded">
                                                            {{ $numeric ++ }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <p class="text-muted mb-1 text-truncate">Next Approval
                                                    </p>
                                                    <div class="fw-semibold font-size-15">{{ $record['waitingapprover'][0]['fullname'] }}</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <h5 class="font-size-14 mb-0 text-truncate w-xs bg-info p-2 rounded text-center">
                                                        {{ $record['code'] }}
                                                    </h5>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- end card -->
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-3">List of Need your Approval</h5>
                            </div>
                        </div>
                        @if ($totalPendingApproval == 0)
                            There are no submissions requiring your approval.
                        @else
                        <div class="mx-n4" data-simplebar style="max-height: 296px;">
                            <ul class="list-unstyled mb-0">
                                @php
                                    $numeric = 1;
                                @endphp
                                @foreach ($listPendingApproval as $tableName => $records)
                                    <li class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-sm">
                                                    <div class="avatar-title bg-success bg-gradient rounded">
                                                        {{ $numeric ++ }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p class="text-muted mb-1 text-truncate">Creator
                                                </p>
                                                <div class="fw-semibold font-size-15">{{ $records->creator }}</div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <h5
                                                    class="font-size-14 mb-0 text-truncate w-xs bg-soft-success p-2 rounded text-center">
                                                    {{ $records->code }}</h5>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

        {{-- <div class="card">
            <div class="card-body pb-0">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-3">Trend Submissions</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="dropdown">
                            <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="fw-semibold">Year :</span> <span class="text-muted">2023<i
                                        class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">2023</a>
                                <a class="dropdown-item" href="#">2022</a>
                                <a class="dropdown-item" href="#">2021</a>
                                <a class="dropdown-item" href="#">2020</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row gy-4">
                    <div class="col-xxl-3">
                        <div>
                            <div class="mt-3 mb-3">
                                <p class="text-muted mb-1">Total</p>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h2 class="mb-0">0</h2>
                                </div>
                            </div>

                            <div class="row g-0">
                                <div class="col-sm-6">
                                    <div class="border-bottom border-end p-3 h-100">
                                        <p class="text-muted text-truncate mb-1">Approved</p>
                                        <h5 class="text-truncate mb-0">0</h5>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border-bottom p-3 h-100">
                                        <p class="text-muted text-truncate mb-1">Rejected</p>
                                        <h5 class="text-truncate mb-0">0</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-0">
                                <div class="col-sm-6">
                                    <div class="border-bottom border-end p-3 h-100">
                                        <p class="text-muted text-truncate mb-1">Rework</p>
                                        <h5 class="text-truncate mb-0">0</h5>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border-bottom p-3 h-100">
                                        <p class="text-muted text-truncate mb-1">Draft</p>
                                        <h5 class="text-truncate mb-0">0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-9">
                        <div>
                            <div id="chart-column" class="apex-charts" data-colors='["#f1f3f7", "#3b76e1"]'></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end card body -->
        </div> --}}
        <!-- end card -->

        
    </div>
    <!-- end col -->
    <div class="col-xxl-3">

        <div class="user-sidebar">
            <div class="card">
                <div class="card-body p-0">
                    <div class="user-profile-img">
                        <img src="{{ URL::asset('assets/images/pattern-bg.jpg') }}" class="profile-img profile-foreground-img rounded-top"
                            style="height: 120px;" alt="">
                        <div class="overlay-content rounded-top">
                            <div>
                                <div class="user-nav p-3">
                                    <div class="d-flex justify-content-end">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i data-eva="more-horizontal-outline" data-eva-width="20"
                                                    data-eva-height="20" class="fill-white"></i>
                                            </a>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><button class="dropdown-item" onClick="uploadButton()">Change Profile Picture</button></li>
                                                <div id="upload-popup">
                                                    <form id="upload-form" method="POST" enctype="multipart/form-data">
                                                        {{-- @csrf --}}
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" id="picture-input" accept="image/*">
                                                            <button class="btn btn-primary" type="button" id="submit-button" onClick="submitButton()">Upload</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end user-profile-img -->

                    <div class="mt-n5 position-relative">
                        <div class="text-center">
                            <img src="@if (Auth::user()->avatar != ''){{ URL::asset('images/' . Auth::user()->avatar) }}@else{{ URL::asset('assets/images/users/avatar-1.jpg') }}@endif" alt=""
                                class="avatar-xl rounded-circle img-thumbnail">

                            <div class="mt-3">
                                <h5 class="mb-1">{{ auth::user()->fullname }}</h5>
                                <p class="text-muted">{{ ($employee) ? $employee->SAPID : 'Not Linked With Employee Data'; }}</p>
                            </div>
                        </div>
                    </div>

                        </div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
    </div>
    <!-- end col -->
</div>
<!-- end row -->

{{-- <div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-3">Ratings & Reviews</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="dropdown">
                            <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i data-eva="more-horizontal-outline" class="fill-muted" data-eva-height="18"
                                    data-eva-width="18"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Yearly</a>
                                <a class="dropdown-item" href="#">Monthly</a>
                                <a class="dropdown-item" href="#">Weekly</a>
                                <a class="dropdown-item" href="#">Today</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row gy-4 gx-0">
                    <div class="col-lg-6">
                        <div>
                            <div class="text-center">
                                <h1>4.3</h1>
                                <div class="font-size-16 mb-1">
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half-full text-warning"></i>
                                </div>
                                <div class="text-muted">(14,254 Based)</div>
                            </div>

                            <div class="mt-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">5 <i class="mdi mdi-star"></i></h5>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1 py-2">
                                            <div class="progress animated-progess custom-progress">
                                                <div class="progress-bar bg-gradient bg-primary" role="progressbar"
                                                    style="width: 90%" aria-valuenow="90" aria-valuemin="0"
                                                    aria-valuemax="90">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">50%</h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">4 <i class="mdi mdi-star"></i></h5>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1 py-2">
                                            <div class="progress animated-progess custom-progress">
                                                <div class="progress-bar bg-gradient bg-primary" role="progressbar"
                                                    style="width: 75%" aria-valuenow="75" aria-valuemin="0"
                                                    aria-valuemax="75">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">20%</h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">3 <i class="mdi mdi-star"></i></h5>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1 py-2">
                                            <div class="progress animated-progess custom-progress">
                                                <div class="progress-bar bg-gradient bg-primary" role="progressbar"
                                                    style="width: 60%" aria-valuenow="60" aria-valuemin="0"
                                                    aria-valuemax="60">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">15%</h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">2 <i class="mdi mdi-star"></i></h5>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1 py-2">
                                            <div class="progress animated-progess custom-progress">
                                                <div class="progress-bar bg-gradient bg-warning" role="progressbar"
                                                    style="width: 50%" aria-valuenow="50" aria-valuemin="0"
                                                    aria-valuemax="50">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">10%</h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">1 <i class="mdi mdi-star"></i></h5>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1 py-2">
                                            <div class="progress animated-progess custom-progress">
                                                <div class="progress-bar bg-gradient bg-danger" role="progressbar"
                                                    style="width: 20%" aria-valuenow="20" aria-valuemin="0"
                                                    aria-valuemax="20">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1 py-2">
                                            <h5 class="font-size-16 mb-0">5%</h5>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>
                    <!-- end col -->

                    <div class="col-lg-6">
                        <div class="ps-lg-4">
                            <div class="d-flex flex-wrap align-items-start gap-3">
                                <h5 class="font-size-15">Reviews: </h5>
                                <p class="text-muted">(14,254 Based)</p>
                            </div>

                            <div class=" me-lg-n3 pe-lg-3" data-simplebar style="max-height: 266px;">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <div>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="badge bg-success bg-gradient mb-2"><i
                                                            class="mdi mdi-star"></i> 4.1</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <p class="text-muted font-size-13">12 Jul, 21</p>
                                                </div>
                                            </div>

                                            <p class="text-muted mb-4">It will be as simple as in fact, It will seem
                                                like simplified</p>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-14 mb-0">Samuel</h5>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <div class="hstack gap-3">
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Like">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-thumb-up-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Comment">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-comment-text-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div class="dropdown">
                                                            <a class="text-muted dropdown-toggle" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#">Action</a>
                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                <a class="dropdown-item" href="#">Something else
                                                                    here</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="badge bg-success bg-gradient mb-2"><i
                                                            class="mdi mdi-star"></i> 4.0</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <p class="text-muted font-size-13">06 Jul, 21</p>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-4">Sed ut perspiciatis iste error sit</p>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-14 mb-0">Joseph</h5>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <div class="hstack gap-3">
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Like">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-thumb-up-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Comment">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-comment-text-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div class="dropdown">
                                                            <a class="text-muted dropdown-toggle" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#">Action</a>
                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                <a class="dropdown-item" href="#">Something else
                                                                    here</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item">
                                        <div>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="badge bg-success bg-gradient mb-2"><i
                                                            class="mdi mdi-star"></i> 4.2</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <p class="text-muted font-size-13">26 Jun, 21</p>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-4">Neque porro quisquam est, qui dolorem ipsum quia
                                                dolor sit amet</p>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-14 mb-0">Paul</h5>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <div class="hstack gap-3">
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Like">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-thumb-up-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Comment">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-comment-text-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div class="dropdown">
                                                            <a class="text-muted dropdown-toggle" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#">Action</a>
                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                <a class="dropdown-item" href="#">Something else
                                                                    here</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="list-group-item">
                                        <div>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="badge bg-success bg-gradient mb-2"><i
                                                            class="mdi mdi-star"></i> 4.1</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <p class="text-muted font-size-13">24 Jun, 21</p>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-4">Ut enim ad minima veniam, quis nostrum ullam
                                                corporis suscipit consequatur nisi ut</p>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="font-size-14 mb-0">Patrick</h5>
                                                </div>

                                                <div class="flex-shrink-0">
                                                    <div class="hstack gap-3">
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Like">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-thumb-up-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Comment">
                                                            <a href="#" class="text-muted"><i
                                                                    class="mdi mdi-comment-text-outline"></i></a>
                                                        </div>
                                                        <div class="vr"></div>
                                                        <div class="dropdown">
                                                            <a class="text-muted dropdown-toggle" href="#"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                            </a>

                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item" href="#">Action</a>
                                                                <a class="dropdown-item" href="#">Another action</a>
                                                                <a class="dropdown-item" href="#">Something else
                                                                    here</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->

    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-3">Transaction</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="dropdown">
                            <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="fw-semibold">Report By:</span> <span class="text-muted">Monthly<i
                                        class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Yearly</a>
                                <a class="dropdown-item" href="#">Monthly</a>
                                <a class="dropdown-item" href="#">Weekly</a>
                                <a class="dropdown-item" href="#">Today</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle">Order ID</th>
                                <th class="align-middle">Billing Name</th>
                                <th class="align-middle">Date</th>
                                <th class="align-middle">Total</th>
                                <th class="align-middle">Pay Status</th>
                                <th class="align-middle">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="javascript: void(0);" class="text-body fw-semibold">#BR2150</a> </td>
                                <td>Smith</td>
                                <td>
                                    07 Oct, 2021
                                </td>
                                <td>
                                    $24.05
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-soft-success font-size-11">Paid</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i data-eva="eye" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-success bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i data-eva="edit" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <i data-eva="trash-2" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><a href="javascript: void(0);" class="text-body fw-semibold">#BR2149</a> </td>
                                <td>James</td>
                                <td>
                                    07 Oct, 2021
                                </td>
                                <td>
                                    $26.15
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-soft-success font-size-11">Paid</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i data-eva="eye" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-success bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i data-eva="edit" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <i data-eva="trash-2" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><a href="javascript: void(0);" class="text-body fw-semibold">#BR2148</a> </td>
                                <td>Jill</td>
                                <td>
                                    06 Oct, 2021
                                </td>
                                <td>
                                    $21.25
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-soft-warning font-size-11">Refund</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i data-eva="eye" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-success bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i data-eva="edit" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <i data-eva="trash-2" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><a href="javascript: void(0);" class="text-body fw-semibold">#BR2147</a> </td>
                                <td>Kyle</td>
                                <td>
                                    05 Oct, 2021
                                </td>
                                <td>
                                    $25.03
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-soft-success font-size-11">Paid</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i data-eva="eye" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-success bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i data-eva="edit" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <i data-eva="trash-2" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><a href="javascript: void(0);" class="text-body fw-semibold">#BR2146</a> </td>
                                <td>Robert</td>
                                <td>
                                    05 Oct, 2021
                                </td>
                                <td>
                                    $22.61
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-pill badge-soft-success font-size-11">Paid</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                            <i data-eva="eye" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-success bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                            <i data-eva="edit" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger bg-gradient btn-sm"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                            <i data-eva="trash-2" data-eva-height="14" data-eva-width="14"
                                                class="fill-white align-text-top"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    <!-- end table -->
                </div>
                <!-- end table responsive -->
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div> --}}
@endsection
@section('script')
<!-- apexcharts -->
<script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- dashboard init -->
<script src="{{ URL::asset('/assets/js/pages/dashboard.init.js') }}"></script>
{{-- <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script> --}}
@endsection
