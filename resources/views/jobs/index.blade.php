<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> --}}

    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: rgb(240, 241, 242);
            /* font-size: 10em; */
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .container {
            max-width: 80% !important;
        }
        nav {
            /* margin-bottom: 20px; */
            padding: 10px !important;
            border-top: 4px solid #28a745;
        }
        .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }
        .job-listings ul {
            list-style-type: none;
            padding: 0;
        }
        .job-listings ul li {
            background: #f8f9fa;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        footer {
            background-color: #f8f9fa;
            padding: 10px 0;
            text-align: center;
        }
        .dropdown-checkboxes .dropdown-menu {
            padding: 10px;
            width: 300px; /* Sesuaikan lebar dropdown */
        }
        .form-check {
            display: flex;
            align-items: center;
            justify-content: start;
            padding: 5px 0;
        }
        .form-check-input {
            width: 20px;
            height: 20px;
        }
        .form-check-label {
            margin-left: 12px;
            font-size: 16px;
        }
        .btn.dropdown-toggle {
            background-color: #fff !important;
            border-color: #6c757d !important; /* Pilihan jika Anda juga ingin menyesuaikan warna border */
            border-width: 3px;
        }
        .carousel-item {
            position: relative;
        }
        .carousel-item .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* 0.5 adalah tingkat opasitas, anda bisa sesuaikan */
            z-index: 1;
        }
        .carousel-item img {
            position:relative;
            z-index: 0; /* Pastikan gambar berada dibawah overlay */
        }

        .carousel-caption {
            z-index: 2; /* Pastikan caption berada di atas overlay */
        }
        .navbar-nav .nav-link {
            color: #333;
            transition: color 0.3s, border-bottom 0.3s; /* Menambahkan transisi halus */
        }

        .navbar-nav .nav-link:hover {
            color: #007bff; /* Ubah warna teks saat hover */
            border-bottom: 1px solid #28a745; /* Menambahkan garis bawah saat hover */
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            margin-right: 10px;
        }
        .navbar-brand .brand-text {
            display: inline-block;
            vertical-align: middle;
            font-weight: bold;
            font-size: 20px;
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#">
                <img src="https://dev.itcihutanimanunggal.co.id/_next/static/media/logo-ihm.13d5fa2e.png" alt="logo" width="50" height="50">
                <span class="brand-text d-none d-md-block">Itci Hutani Manunggal</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a class="nav-link" href="#"><strong class="font-weight-bold">Home</strong></a></li>
                    <li class="nav-item active"><a class="nav-link" href="#"><strong class="font-weight-bold">About Us</strong></a></li>
                    <li class="nav-item active"><a class="nav-link" href="#"><strong class="font-weight-bold">Contact</strong></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Carousel -->
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="overlay"></div>
                    <img src="https://admin.itcihutanimanunggal.co.id/storage/uploads/slides/aG9tZS0x_64f1438d927cd.jpeg" class="d-block w-100" alt="...">
                    {{-- <div class="carousel-caption d-none d-md-block"> --}}
                    <div class="carousel-caption">
                        <h5>Reach Your Career Goals</h5>
                        <p>Explore opportunities that match your skills.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="overlay"></div>
                    <img src="https://admin.itcihutanimanunggal.co.id/storage/uploads/slides/aG9tZS0x_64f1438d8b88c.jpeg" class="d-block w-100" alt="...">
                    {{-- <div class="carousel-caption d-none d-md-block"> --}}
                    <div class="carousel-caption">
                        <h5>Vast Network</h5>
                        <p>Connect with top companies worldwide.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="overlay"></div>
                    <img src="https://admin.itcihutanimanunggal.co.id/storage/uploads/slides/aG9tZS0x_64f1438d93b9e.jpeg" class="d-block w-100" alt="...">
                    {{-- <div class="carousel-caption d-none d-md-block"> --}}
                    <div class="carousel-caption">
                        <h5>Get Noticed</h5>
                        <p>Stand out to your dream employers.</p>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="container">
            <section class="search py-4 px-4 bg-light">
            <h2 class="text-center">Find Your Dream Job</h2>
            <div class="form-inline justify-content-center">
                <div class="input-group">
                    <input type="text" name="keywords" class="form-control" placeholder="Keywords">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-search"></i> Search <!-- Menggunakan ikon Font Awesome -->
                        </button>
                    </div>
                </div>
            </div>
            <!-- Job Category Dropdown -->
            <div class="form-inline justify-content-center mt-2">
                <div class="dropdown show dropdown-checkboxes">
                    <button class="btn dropdown-toggle mr-2" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Select Job Category
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="category1">
                            <label class="form-check-label" for="category1">Development</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="category2">
                            <label class="form-check-label" for="category2">Design</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="category3">
                            <label class="form-check-label" for="category3">Marketing</label>
                        </div>
                        <!-- Add more categories as needed -->
                        <div class="dropdown-divider"></div>
                        <button type="button" class="btn btn-success btn-sm w-100 mb-2" id="viewJobsButton">View Jobs</button>
                        <button type="button" class="btn btn-secondary btn-sm w-100" id="clearButton">Clear</button>
                    </div>
                </div>
            </div>

        </section>
        </div>

    <!-- Job Listings -->
    <section class="job-listings py-4">
        <div class="container">
            <h2 class="mb-4">Latest Job Listings</h2>
            <ul>
                <li>
                    <h3>Web Developer</h3>
                    <p>Company: ABC Tech</p>
                    <p>Location: India</p>
                    <p>Description: Good Web Developer</p>
                    <a href="#" class="btn btn-outline-primary">Apply Now</a>
                </li>
                <li>
                    <h3>Graphic Designer</h3>
                    <p>Company: XYZ Design</p>
                    <p>Location: India</p>
                    <p>Description: Good Graphic Designer</p>
                    <a href="#" class="btn btn-outline-primary">Apply Now</a>
                </li>
            </ul>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Job Search Portal. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS, jQuery, and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            $('#clearButton').on('click', function(event) {
                event.preventDefault(); // Mencegah aksi default, menutup dropdown
                $('.form-check-input').prop('checked', false);
            });

            $('#viewJobsButton').on('click', function() {
                $('#dropdownMenuButton').dropdown('toggle');
                alert('Jobs filter applied based on selected categories.');
            });
        });
    </script>
</body>
</html>
