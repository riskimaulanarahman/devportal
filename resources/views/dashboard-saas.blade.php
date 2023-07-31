@extends('layouts.master')
@section('title') @lang('translation.Saas') @endsection
@section('css')
    <link href="{{ URL::asset('assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('assets/libs/swiper/swiper.min.css') }}" rel="stylesheet">
@endsection
@section('content')


@section('pagetitle')Saas @endsection


    <div class="row">
        <div class="col-xl-5">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary bg-gradient">
                                                <i data-eva="shopping-bag" class="fill-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="text-muted"><i data-eva="diagonal-arrow-right-up"
                                                data-eva-width="18" data-eva-height="18"
                                                class="align-text-bottom fill-success me-1"></i> 1.02%</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h4 class="text-truncate mb-1">$34,123</h4>
                                            <p class="text-truncate text-muted mb-0">Total Sales</p>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div id="chart-sparkline1" data-colors='["#63ad6f"]'></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->

                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary bg-gradient">
                                                <i data-eva="pie-chart-2" class="fill-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="text-muted"><i data-eva="diagonal-arrow-left-down"
                                                data-eva-width="18" data-eva-height="18"
                                                class="align-text-bottom fill-danger me-1"></i> 0.2%</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h4 class="text-truncate mb-1">$21,456</h4>
                                            <p class="text-truncate text-muted mb-0">Av. Sales Yearly</p>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div id="chart-sparkline2" data-colors='["#f56e6e"]'></div>
                                        </div>
                                    </div>
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
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary bg-gradient">
                                                <i data-eva="activity" class="fill-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="text-muted"><i data-eva="diagonal-arrow-left-down"
                                                data-eva-width="18" data-eva-height="18"
                                                class="align-text-bottom fill-danger me-1"></i> 0.1%</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h4 class="text-truncate mb-1">10.23%</h4>
                                            <p class="text-truncate text-muted mb-0">Profit Growth</p>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div id="chart-sparkline3" data-colors='["#f56e6e"]'></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->

                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="avatar">
                                            <div class="avatar-title rounded bg-primary bg-gradient">
                                                <i data-eva="people" class="fill-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="text-muted"><i data-eva="diagonal-arrow-right-up"
                                                data-eva-width="18" data-eva-height="18"
                                                class="align-text-bottom fill-success me-1"></i> 1.15%</div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h4 class="text-truncate mb-1">20.4k</h4>
                                            <p class="text-truncate text-muted mb-0">Users</p>
                                        </div>
                                        <div class="flex-shrink-0 align-self-center">
                                            <div id="chart-sparkline4" data-colors='["#63ad6f"]'></div>
                                        </div>
                                    </div>
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
        </div>
        <!-- end col -->

        <div class="col-xl-7">
            <div class="card bg-primary bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title text-white mb-3">Global Statistics</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i data-eva="more-horizontal-outline" class="fill-white" data-eva-height="18"
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

                    <div class="row">
                        <div class="col-lg-7">
                            <div id="world-map-markers" style="height: 275px;"></div>
                        </div>
                        <!-- end col -->
                        <div class="col-lg-5">
                            <div>
                                <!-- Swiper -->
                                <div class="swiper swiper-location-widget overflow-hidden">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 align-self-center me-3">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-light rounded">
                                                                    <img src="{{ URL::asset('assets/images/flags/us.jpg') }}" alt=""
                                                                        height="10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1">United States</p>
                                                            <h5 class="font-size-16 mb-0">81%</h5>
                                                        </div>
                                                        <div class="flex-shrink-0 align-self-end ms-2">
                                                            <div class="text-muted"><i
                                                                    data-eva="diagonal-arrow-right-up" data-eva-width="18"
                                                                    data-eva-height="18"
                                                                    class="align-text-bottom fill-success me-1"></i> 1.05%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!-- end slide item -->
                                        <div class="swiper-slide">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 align-self-center me-3">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-light rounded">
                                                                    <img src="{{ URL::asset('assets/images/flags/spain.jpg') }}" alt=""
                                                                        height="10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1">Spain</p>
                                                            <h5 class="font-size-16 mb-0">77%</h5>
                                                        </div>
                                                        <div class="flex-shrink-0 align-self-end ms-2">
                                                            <div class="text-muted"><i
                                                                    data-eva="diagonal-arrow-right-up" data-eva-width="18"
                                                                    data-eva-height="18"
                                                                    class="align-text-bottom fill-success me-1"></i> 0.24%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!-- end slide item -->
                                        <div class="swiper-slide">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 align-self-center me-3">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-light rounded">
                                                                    <img src="{{ URL::asset('assets/images/flags/germany.jpg') }}" alt=""
                                                                        height="10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1">Germany</p>
                                                            <h5 class="font-size-16 mb-0">80%</h5>
                                                        </div>
                                                        <div class="flex-shrink-0 align-self-end ms-2">
                                                            <div class="text-muted"><i
                                                                    data-eva="diagonal-arrow-right-up" data-eva-width="18"
                                                                    data-eva-height="18"
                                                                    class="align-text-bottom fill-success me-1"></i> 0.18%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!-- end slide item -->
                                        <div class="swiper-slide">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0 align-self-center me-3">
                                                            <div class="avatar-sm">
                                                                <div class="avatar-title bg-light rounded">
                                                                    <img src="{{ URL::asset('assets/images/flags/italy.jpg') }}" alt=""
                                                                        height="10">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted mb-1">Italy</p>
                                                            <h5 class="font-size-16 mb-0">73%</h5>
                                                        </div>
                                                        <div class="flex-shrink-0 align-self-end ms-2">
                                                            <div class="text-muted"><i
                                                                    data-eva="diagonal-arrow-right-up" data-eva-width="18"
                                                                    data-eva-height="18"
                                                                    class="align-text-bottom fill-success me-1"></i> 0.15%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end card body -->
                                            </div>
                                            <!-- end card -->
                                        </div>
                                        <!-- end slide item -->
                                    </div>
                                    <!-- end slider wrapper -->
                                </div>
                                <!-- end slider -->

                            </div>
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->
                </div>
                <!-- end card body -->
            </div>
            <!-- end card  -->
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body pb-0">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4">Analytics Overview</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="fw-semibold">Sort By:</span> <span class="text-muted">Yearly<i
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
                    <!-- end head -->

                    <div>
                        <div class="row">
                            <div class="col-lg-3 col-sm-6">
                                <div class="p-3 bg-soft-light border border-light rounded mb-lg-0 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-muted text-truncate mb-1">Orders</p>
                                            <h4 class="text-truncate mb-0">5,643</h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-end">
                                            <div class="text-muted"><i data-eva="diagonal-arrow-right-up"
                                                    data-eva-width="18" data-eva-height="18"
                                                    class="align-text-bottom fill-success"></i> 1.02%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="p-3 bg-soft-light border border-light rounded mb-lg-0 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-muted text-truncate mb-1">Sales</p>
                                            <h4 class="text-truncate mb-0">16,273</h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-end">
                                            <div class="text-muted"><i data-eva="diagonal-arrow-left-down"
                                                    data-eva-width="18" data-eva-height="18"
                                                    class="align-text-bottom fill-danger"></i> 0.2%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="p-3 bg-soft-light border border-light rounded mb-lg-0 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-muted text-truncate mb-1">Order Value</p>
                                            <h4 class="text-truncate mb-0">12.03 %</h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-end">
                                            <div class="text-muted"><i data-eva="diagonal-arrow-left-down"
                                                    data-eva-width="18" data-eva-height="18"
                                                    class="align-text-bottom fill-danger"></i> 0.1%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="p-3 bg-soft-light border border-light rounded mb-lg-0 mb-3">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <p class="text-muted text-truncate mb-1">Customers</p>
                                            <h4 class="text-truncate mb-0">21,456</h4>
                                        </div>
                                        <div class="flex-shrink-0 align-self-end">
                                            <div class="text-muted"><i data-eva="diagonal-arrow-right-up"
                                                    data-eva-width="18" data-eva-height="18"
                                                    class="align-text-bottom fill-success"></i> 1.15%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-n2">
                        <div id="chart-area" class="apex-charts"
                            data-colors='["#f1f3f7", "#f56e6e", "#f1f3f7", "#3b76e1"]'></div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-2">Statistics</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="text-muted">Monthly<i class="mdi mdi-chevron-down ms-1"></i></span>
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
                    <!-- end head -->

                    <div>
                        <div id="chart-radialBar" class="apex-charts" data-colors='["#3b76e1", "#f56e6e", "#f9c256"]'>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between border-bottom pb-3">
                            <p class="mb-0"><i class="mdi mdi-circle font-size-12 text-primary me-1"></i> Smart
                                Camera</p>
                            <div>
                                <span class="pe-5"> 56,236</span>
                                <span class="badge badge-soft-success font-size-12"> + 0.2% </span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-3">
                            <p class="mb-0"><i class="mdi mdi-circle font-size-12 text-danger me-1"></i> Ipad
                                2021 Modal</p>
                            <div>
                                <span class="pe-5"> 220k</span>
                                <span class="badge badge-soft-danger font-size-12"> - 0.7% </span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between pt-3">
                            <p class="mb-0"><i class="mdi mdi-circle font-size-12 text-warning me-1"></i> New
                                Smart 4k TV</p>
                            <div>
                                <span class="pe-5"> 51,568</span>
                                <span class="badge badge-soft-success font-size-12"> + 0.4% </span>
                            </div>
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
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4">Activities</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="text-muted">Monthly<i class="mdi mdi-chevron-down ms-1"></i></span>
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
                    <!-- end head -->

                    <ul class="list-unstyled mb-0" data-simplebar style="max-height: 400px;">
                        <li class="py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-4">
                                    <div class="avatar-md h-auto p-1 py-2 bg-light rounded">
                                        <div class="text-center">
                                            <h5 class="mb-0">12</h5>
                                            <div>Sep</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-size-15">Darlene Thibodeau</h5>
                                    <p class="text-muted mb-2">Everyone realizes why a new common velit ut language would
                                        be desirable... <a href="#">Read more</a></p>

                                </div>
                            </div>
                        </li>

                        <li class="py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-4">
                                    <div class="avatar-md h-auto p-1 py-2 bg-light rounded">
                                        <div class="text-center">
                                            <h5 class="mb-0">11</h5>
                                            <div>Sep</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-size-15">Create New project Bundling Product</h5>

                                    <div class="avatar-group mb-2">
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('assets/images/users/avatar-4.jpg') }}" alt=""
                                                    class="rounded-circle avatar-sm">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('assets/images/users/avatar-5.jpg') }}" alt=""
                                                    class="rounded-circle avatar-sm">
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <div class="avatar-sm">
                                                    <span
                                                        class="avatar-title rounded-circle bg-success text-white font-size-16">
                                                        A
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="avatar-group-item">
                                            <a href="javascript: void(0);" class="d-inline-block">
                                                <img src="{{ URL::asset('assets/images/users/avatar-2.jpg') }}" alt=""
                                                    class="rounded-circle avatar-sm">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-4">
                                    <div class="avatar-md h-auto p-1 py-2 bg-light rounded">
                                        <div class="text-center">
                                            <h5 class="mb-0">10</h5>
                                            <div>Sep</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-size-15">Aenean dictum placerat vestibulum</h5>
                                    <p class="text-muted mb-2">Zack Wetass, finibus, venenatis lacinia vel elementum
                                        Commented <span class="text-primary"> Developer Moreno</span></p>
                                </div>
                            </div>
                        </li>

                        <li class="py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-4">
                                    <div class="avatar-md h-auto p-1 py-2 bg-light rounded">
                                        <div class="text-center">
                                            <h5 class="mb-0">9</h5>
                                            <div>Sep</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-size-15">Adding a new event with attachments</h5>
                                    <div class="d-flex flex-wrap align-items-start event-img mt-3 gap-2">
                                        <img src="{{ URL::asset('assets/images/small/img-1.jpg') }}" alt="" class="img-fluid rounded"
                                            width="80">
                                        <img src="{{ URL::asset('assets/images/small/img-2.jpg') }}" alt="" class="img-fluid rounded"
                                            width="80">
                                        <img src="{{ URL::asset('assets/images/small/img-5.jpg') }}" alt="" class="img-fluid rounded"
                                            width="80">
                                    </div>

                                </div>
                            </div>
                        </li>
                        <li class="pt-2">
                            <a href="#" class="btn btn-link w-100 shadow-none"><i
                                    class="mdi mdi-loading mdi-spin me-2"></i> Load More</a>
                        </li>
                    </ul>



                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-4">Inbox</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span class="text-muted">Monthly<i class="mdi mdi-chevron-down ms-1"></i></span>
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
                    <!-- end head -->

                    <div class="chat-message-list widget-chat-list mx-n3" data-simplebar>
                        <div class="px-3">
                            <ul class="list-unstyled chat-list">
                                <li class="active">
                                    <a href="#" class="mt-0">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                <img src="{{ URL::asset('assets/images/users/avatar-2.jpg') }}"
                                                    class="rounded-circle avatar-sm" alt="">
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Steven Jones</h5>
                                                        <p class="text-truncate mb-0">Hey! there I'm available</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">02 min</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li class="unread">
                                    <a href="#">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                <div class="avatar-sm align-self-center">
                                                    <span class="avatar-title rounded-circle bg-primary bg-gradient">
                                                        H
                                                    </span>
                                                </div>
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Alex Mulvey</h5>
                                                        <p class="text-truncate mb-0">I've finished it! See you so</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">10 min</div>
                                                    </div>

                                                    <div class="unread-message">
                                                        <span class="badge bg-danger rounded-pill">1</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 user-img away align-self-center me-3">
                                                <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}"
                                                    class="rounded-circle avatar-sm" alt="">
                                                <span class="user-status"></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Justin Moore</h5>
                                                        <p class="text-truncate mb-0">This theme is awesome!</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">22 min</div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">

                                            <div class="flex-shrink-0 user-img align-self-center me-3">
                                                <img src="{{ URL::asset('assets/images/users/avatar-4.jpg') }}"
                                                    class="rounded-circle avatar-sm" alt="">
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Edith McLain</h5>
                                                        <p class="text-truncate mb-0">Nice to meet you</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">01 Hr</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">

                                            <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                <div class="avatar-sm align-self-center">
                                                    <span class="avatar-title rounded-circle bg-primary bg-gradient">
                                                        W
                                                    </span>
                                                </div>
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">William Declue</h5>
                                                        <p class="text-truncate mb-0">Wow that's great</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">04 Hrs</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">

                                            <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                <div class="avatar-sm align-self-center">
                                                    <span class="avatar-title rounded-circle bg-primary bg-gradient">
                                                        W
                                                    </span>
                                                </div>
                                                <span class="user-status"></span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Wendy Lennox</h5>
                                                        <p class="text-truncate mb-0">Hey! there I'm available</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">10 Hrs</div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">

                                            <div class="flex-shrink-0 user-img online align-self-center me-3">
                                                <div class="avatar-sm align-self-center">
                                                    <span class="avatar-title rounded-circle bg-primary bg-gradient">
                                                        W
                                                    </span>
                                                </div>
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Warren Daly</h5>
                                                        <p class="text-truncate mb-0">Hey! there I'm available</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">10 Hrs</div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                                <li>
                                    <a href="#">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0 user-img away align-self-center me-3">
                                                <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}"
                                                    class="rounded-circle avatar-sm" alt="">
                                                <span class="user-status"></span>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate font-size-14 mb-1">Mary Welch</h5>
                                                        <p class="text-truncate mb-0">This theme is awesome!</p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="font-size-11">19 Sept</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <!-- end li -->
                            </ul>
                            <!-- end ul -->
                        </div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-xl-4">
            <div class="card">
                <div class="p-3 border-bottom">
                    <div class="row">
                        <div class="col-xl-4 col-7">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 avatar me-3 d-sm-block d-none">
                                    <img src="{{ URL::asset('assets/images/users/avatar-2.jpg') }}" alt=""
                                        class="img-thumbnail d-block rounded-circle">
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="font-size-14 mb-1 text-truncate"><a href="#" class="text-dark">Steven
                                            Jones</a></h5>
                                    <p class="text-muted text-truncate mb-0"><i
                                            class="mdi mdi-circle font-size-12 text-success"></i> Online</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-5">
                            <ul class="list-inline user-chat-nav text-end mb-0">
                                <li class="list-inline-item">
                                    <div class="dropdown">
                                        <button class="btn nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-search"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-2">
                                            <form class="px-2">
                                                <div>
                                                    <input type="text" class="form-control bg-light rounded"
                                                        placeholder="Search...">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                                <!-- end li -->
                                <li class="list-inline-item">
                                    <div class="dropdown">
                                        <button class="btn nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Profile</a>
                                            <a class="dropdown-item" href="#">Archive</a>
                                            <a class="dropdown-item" href="#">Muted</a>
                                            <a class="dropdown-item" href="#">Delete</a>
                                        </div>
                                    </div>
                                </li>
                                <!-- end li -->
                            </ul>
                            <!-- end ul -->
                        </div>
                    </div>
                </div>
                <div>
                    <div class="chat-conversation p-3 widget-chat" data-simplebar>
                        <ul class="list-unstyled mb-0">
                            <li class="chat-day-title">
                                <div class="title">Today</div>
                            </li>
                            <li>
                                <div class="conversation-list">
                                    <div class="d-flex">
                                        <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:00
                                                            AM</span></div>
                                                    <p class="mb-0">Good Morning</p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li class="right">
                                <div class="conversation-list">
                                    <div class="d-flex">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:02
                                                            AM</span></div>
                                                    <p class="mb-0">Good morning</p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <img src="{{ URL::asset('assets/images/users/avatar-6.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                    </div>

                                </div>

                            </li>

                            <li>
                                <div class="conversation-list">
                                    <div class="d-flex">
                                        <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:04
                                                            AM</span></div>
                                                    <p class="mb-0">
                                                        Hi there, How are you?
                                                    </p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex">
                                        <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:04
                                                            AM</span></div>
                                                    <p class="mb-0">
                                                        Waiting for your reply.As I heve to go back soon. i have to travel
                                                        long distance.
                                                    </p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>

                            </li>

                            <li class="right">
                                <div class="conversation-list">
                                    <div class="d-flex">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:08
                                                            AM</span></div>
                                                    <p class="mb-0">
                                                        Hi, I am coming there in few minutes. Please wait!! I am in taxi
                                                        right now.
                                                    </p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <img src="{{ URL::asset('assets/images/users/avatar-6.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                    </div>
                                </div>

                            </li>

                            <li>
                                <div class="conversation-list">
                                    <div class="d-flex">
                                        <img src="{{ URL::asset('assets/images/users/avatar-3.jpg') }}" class="rounded-circle avatar-sm"
                                            alt="">
                                        <div class="flex-1">
                                            <div class="ctext-wrap">
                                                <div class="ctext-wrap-content">
                                                    <div class="conversation-name"><span class="time">10:06
                                                            AM</span></div>
                                                    <p class="mb-0">
                                                        Thank You very much, I am waiting here at StarBuck cafe.
                                                    </p>
                                                </div>
                                                <div class="dropdown align-self-start">
                                                    <a class="dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#">Copy</a>
                                                        <a class="dropdown-item" href="#">Save</a>
                                                        <a class="dropdown-item" href="#">Forward</a>
                                                        <a class="dropdown-item" href="#">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </li>

                        </ul>
                        <!-- end ul -->
                    </div>
                </div>
                <div class="p-3 chat-input-section">
                    <div class="row">
                        <div class="col">
                            <div class="position-relative">
                                <input type="text" class="form-control chat-input" placeholder="Enter Message...">

                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary chat-send w-md"><span
                                    class="d-none d-sm-inline-block me-2">Send</span> <i
                                    class="mdi mdi-send float-end"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/swiper/swiper.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/dashboard-saas.init.js') }}"></script>

    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
