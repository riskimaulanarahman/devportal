@extends('layouts.master-without-nav')
@section('title')
@lang('translation.Login')
@endsection
@section('content')

        <div class="auth-page">
            <div class="container-fluid p-0">
                <div class="row g-0 align-items-center">
                    <div class="col-xxl-4 col-lg-4 col-md-6">
                        <div class="row justify-content-center g-0">
                            <div class="col-xl-9">
                                <div class="p-4">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <div class="auth-full-page-content rounded d-flex p-3 my-2">
                                                <div class="w-100">
                                                    <div class="d-flex flex-column h-100">
                                                        <div class="mb-4 mb-md-1">
                                                            <a href="{{ url('/') }}" class="d-block auth-logo">
                                                                {{-- <img src="{{ URL::asset('assets/images/oaslogo.png')}}" alt="" height="250" class="auth-logo-dark me-start"> --}}
                                                                {{-- <img src="{{ URL::asset('assets/images/logo-light.png')}}" alt="" height="22" class="auth-logo-light me-start"> --}}
                                                                <center><b style="font-size: 30px;">{{ env('APP_NAME') }}</b></center>
                                                            </a>
                                                        </div>
                                                        <div class="auth-content my-auto">
                                                            <div class="text-center">
                                                                {{-- <h5 class="mb-0">Online Approval System</h5> --}}
                                                                {{-- <p class="text-muted mt-2">Application Management & Ticketing System</p> --}}
                                                            </div>
                                                            <form class="mt-4 pt-2" id="loginForm" action="{{ route('login') }}" method="POST">
                                                                @csrf
                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" id="input-username" placeholder="Enter User Name" name="username">
                                                                    @error('username')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <label for="input-username">Username</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-eva="people-outline"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                                                    <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" name="password" id="password-input" placeholder="Enter Password">
                                                                    @error('password')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                                        <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                                    </button>
                                                                    <label for="input-password">Password</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-feather="lock"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <button class="btn btn-primary w-100 waves-effect waves-light" type="button" onclick="formSubmit()">Log In</button>
                                                                </div>
                                                                @if(session('error'))
                                                                    <div class="alert alert-danger">
                                                                        {{ session('error') }}
                                                                    </div>
                                                                @endif
                                                            </form>
                                                            <div>
                                                                {{-- <a href="{{ env('APP_URL') }}/public/upload/DevPortal Panduan.pdf" target="_blank"> --}}
                                                                    {{-- </a> --}}
                                                                    
                                                            </div>
                                                        </div>
                                                        <div class="mt-1 text-center">
                                                            <button id="downloadBtn" class="btn btn-danger" data-bs-toggle="modal"
                                                                    data-bs-target=".bs-modal-panduan"><i class="fa fa-download"></i> Download Panduan <i class="fa fa-download"></i></button>
                                                            <p class="mb-0 mt-4"><b>© <script>document.write(new Date().getFullYear())</script> {{ env('APP_NAME') }} </b>. Crafted with <i class="mdi mdi-heart text-danger"></i><br>by <b>{{ env('APP_AUTHOR') }}</b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end auth full page content -->
                    </div>
                    <!-- end col -->
                    <div class="col-xxl-8 col-lg-8 col-md-6">
                        <div class="auth-bg bg-white py-md-5 p-4 d-flex">
                            <div class="bg-overlay bg-white"></div>
                            <!-- end bubble effect -->
                            <div class="row justify-content-center align-items-center" style="width: 100%;">
                                <div class="col-xl-12">
                                  
                                    <div class="p-0 p-sm-4 px-xl-0 py-5">
                                        <div id="reviewcarouselIndicators" class="carousel slide auth-carousel" data-bs-ride="carousel">
                                            <div class="carousel-indicators carousel-indicators-rounded">
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                                {{-- <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="4" aria-label="Slide 5"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="5" aria-label="Slide 6"></button> --}}
                                            </div>

                                            <!-- end carouselIndicators -->
                                            <div class="carousel-inner w-75 mx-auto">
                                                <div class="carousel-item active">
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
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4 badge" style="background-color: orange">Project - On Progress</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Sustaining Efforts to Complete.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="carousel-item">
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
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4 badge bg-primary">Project - Waiting List</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Effective Project Management and Efficient Waiting List Organization.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="carousel-item">
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
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4 badge bg-success">Project - Completed</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Celebrating Our Success &#127881.
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>
                                            
                                            <!-- end carousel-inner -->
                                        </div>
                                        <!-- end review carousel -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container fluid -->
        </div>

        <div class="modal fade bs-modal-panduan" tabindex="-1" role="dialog"
            aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mySmallModalLabel">Panduan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/DevPortal Panduan.pdf', '_blank')">Project Management & Ticket Request</button></li>
                            <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/SKYMAP System.pdf', '_blank')">SKYMAP</button></li>
                        </ul>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        @endsection
        @section('script')
            <script src="{{ URL::asset('assets/js/pages/pass-addon.init.js') }}"></script>
            <script src="{{ URL::asset('assets/js/pages/eva-icon.init.js') }}"></script>
            <script>
                $('#input-username, #password-input').on('keypress', function(e) {
                    if (e.which == 13) { // 13 adalah kode tombol enter
                        formSubmit(); // Panggil fungsi formSubmit di sini
                    }
                });
                const formSubmit = async () => {

                    const username = $('#input-username').val();
                    const password = $('#password-input').val();

                    if(username == '' || password == '') {
                        Swal.fire(
                            {
                                title: 'Error!',
                                text: 'Please enter your login information.',
                                icon: 'error',
                                showCancelButton: false,
                                confirmButtonColor: '#3b76e1',
                            }
                        )
                    } else {

                        const formData = new FormData();

                        formData.append('username', username);
                        formData.append('password', password);

                        const response = await fetch('api/getlogin',{
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if(data.code == 0) {

                            // $("#loginForm").submit();
                            Swal.fire(
                                {
                                    title: 'Error!',
                                    text: 'User Not Found.',
                                    icon: 'error',
                                    showCancelButton: false, 
                                    confirmButtonColor: '#3b76e1',
                                }
                            )

                        } else if(data.code == 404) {

                            Swal.fire(
                                {
                                    title: 'Error!',
                                    text: 'Your login is not linked to any employee information.',
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3b76e1',
                                }
                            )
                            
                        } else if(data.code == 409) {

                            Swal.fire(
                                {
                                    title: 'Error!',
                                    html: 'Someone has accessed your account. <br> ( ip : '+data.ip+' )',
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3b76e1',
                                }
                            )

                        } else if(data.code == 200) {
                            $("#loginForm").submit();
                        }
                    }

                }
            </script>
        @endsection
