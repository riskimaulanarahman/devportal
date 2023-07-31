@extends('layouts.master-without-nav')
@section('title')
@lang('translation.Register')
@endsection
@section('content')   <body>
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
                                                        <div class="mb-4 mb-md-5">
                                                            <a href="index" class="d-block auth-logo">
                                                                <img src="{{URL::asset('assets/images/logo-dark.png')}}"  height="22" class="auth-logo-dark me-start">
                                                                <img src="{{URL::asset('assets/images/logo-light.png')}}"  height="22" class="auth-logo-light me-start">
                                                            </a>
                                                        </div>
                                                        <div class="auth-content my-auto">
                                                            <div class="text-center">
                                                                <h5 class="mb-0">Register Account</h5>
                                                                <p class="text-muted mt-2">Get your free Borex account now.</p>
                                                            </div>
                                                            <form class="needs-validation mt-4 pt-2" novalidate method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                                                                @csrf
                                                                    <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" id="input-email" placeholder="Enter Email" required>
                                                                    @error('email')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <label for="input-email">Email</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-eva="email-outline"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" id="input-username" placeholder="Enter User Name" required>
                                                                    @error('name')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <label for="input-username">Username</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-eva="people-outline"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"  id="input-password" placeholder="Enter Password" required>
                                                                    @error('password')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <label for="input-password">Password</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-eva="lock-outline"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation"  id="input-password" placeholder="Enter Password" required>

                                                                    <label for="input-password">Confirm Password</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-feather="lock"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar"  id="input-avatar" required>
                                                                    @error('avatar')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <div class="form-floating-icon">
                                                                        <i data-feather="file"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-4">
                                                                    <p class="mb-0">By registering you agree to the Borex <a href="#" class="text-primary">Terms of Use</a></p>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Register</button>
                                                                </div>
                                                            </form>

                                                            <div class="mt-4 pt-3 text-center">
                                                                <div class="signin-other-title">
                                                                    <h5 class="font-size-14 mb-4 text-muted fw-medium">- Or you can join with -</h5>
                                                                </div>

                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-soft-primary waves-effect waves-light w-100">
                                                                        <i class="bx bxl-facebook font-size-16 align-middle"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-soft-info waves-effect waves-light w-100">
                                                                        <i class="bx bxl-linkedin font-size-16 align-middle"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-soft-danger waves-effect waves-light w-100">
                                                                        <i class="bx bxl-google font-size-16 align-middle"></i>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="mt-4 pt-3 text-center">
                                                                <p class="text-muted mb-0">Already have an account ? <a href="login" class="text-primary fw-semibold"> Login </a> </p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-4 text-center">
                                                            <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> Borex   . Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
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
                            <div class="row justify-content-center align-items-center">
                                <div class="col-xl-8">
                                    <div class="mt-4">
                                        <img src="{{ URL::asset('./assets/images/login-img.png') }}" class="img-fluid" alt="">
                                    </div>
                                    <div class="p-0 p-sm-4 px-xl-0 py-5">
                                        <div id="reviewcarouselIndicators" class="carousel slide auth-carousel" data-bs-ride="carousel">
                                            <div class="carousel-indicators carousel-indicators-rounded">
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                                            </div>

                                            <!-- end carouselIndicators -->
                                            <div class="carousel-inner w-75 mx-auto">
                                                <div class="carousel-item active">
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4">“I feel confident
                                                            imposing change
                                                            on myself”
                                                        </h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">Vestibulum auctor orci in risus iaculis consequat suscipit felis rutrum aliquet iaculis
                                                            augue sed tempus In elementum ullamcorper lectus vitae pretium Aenean sed odio dolor Nullam ultricies diam
                                                            eu ultrices tellus eifend sagittis.</p>
                                                    </div>
                                                </div>

                                                <div class="carousel-item">
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4">“Our task must be to
                                                            free widening our circle”</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Curabitur eget nulla eget augue dignissim condintum Nunc imperdiet ligula porttitor commodo elementum
                                                            Vivamus justo risus fringilla suscipit faucibus orci luctus
                                                            ultrices posuere cubilia curae lectus non ultricies cursus.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="carousel-item">
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4">“I've learned that
                                                            people will forget what you”</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Pellentesque lacinia scelerisque arcu in aliquam augue molestie rutrum magna Fusce dignissim dolor id auctor accumsan
                                                            vehicula dolor
                                                            vivamus feugiat odio erat sed vehicula lorem tempor quis Donec nec scelerisque magna
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

        @endsection
        @section('script')
            {{-- <script src="{{ URL::asset('assets/js/pages/pass-addon.init.js') }}"></script> --}}
            <script src="{{ URL::asset('assets/js/pages/eva-icon.init.js') }}"></script>
        @endsection
