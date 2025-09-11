<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>SIAP - Sistem Informasi Akademik Polsa </title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="{{ asset('/images/logomini.png') }}" rel="icon">
    <link href="{{ asset('/') }}land/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('/') }}land/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}land/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('/') }}land/vendor/aos/aos.css" rel="stylesheet">
    <link href="{{ asset('/') }}land/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}land/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('/') }}land/css/main.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <style>
        #pdf-container {
            width: 100%;
            height: auto;
            overflow: hidden;
            display: flex;
            justify-content: center;
        }

        #pdf-canvas {
            width: 100%;
            height: auto;
            display: block;
        }


        .portfolio.academic-calendar {
            padding: 60px 0;
            position: relative;
            background: url('path-to-wave-image.png') no-repeat center top;
            background-size: cover;
        }

        .portfolio.academic-calendar .portfolio-content {
            position: relative;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .portfolio.academic-calendar .portfolio-content:hover {
            transform: translateY(-10px);
        }

        .portfolio.academic-calendar .portfolio-content.shadow-lg:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
    </style>


    <!-- =======================================================
  * Template Name: FlexStart
  * Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
  * Updated: Nov 01 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="{{ asset('images/logo1.png') }}" alt="">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="/" class="active">Beranda<br></a></li>
                    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="btn-getstarted flex-md-shrink-0" href="/login">Login</a>

        </div>
    </header>

    <main class="main">
        <!-- Hero Section -->
        <section id="hero" class="hero section">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
                        <h1 data-aos="fade-up">Sistem Informasi Akademik Polsa</h1>
                        <p data-aos="fade-up" data-aos-delay="100">
                        <p>Sistem informasi akademik yang mengelola data mahasiswa, dosen, dan administrasi di
                            Politeknik Sawunggali Aji.</p>
                        </p>
                        <div class="d-flex flex-column flex-md-row" data-aos="fade-up" data-aos-delay="200">
                            <a href="/login" class="btn-get-started">Login <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out">
                        <img src="{{ asset('images/hero-img.png') }}" class="img-fluid animated" alt="">
                    </div>
                </div>
            </div>

        </section>


        <section id="portfolio" class="portfolio section academic-calendar">
            <!-- Section Title -->
            <div class="container section-title text-center" data-aos="fade-up">
                <h2>Kalender Akademik</h2>
                <p>Informasi terbaru mengenai jadwal akademik</p>
            </div>

            <div class="container">
                <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">
                    <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
                    </ul>
                    <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="col-lg-8 col-md-10">
                            <div class="portfolio-item isotope-item filter-app text-center">
                                <div class="portfolio-content">
                                    <!-- PDF Viewer -->
                                    <div id="pdf-container">
                                        <canvas id="pdf-canvas"></canvas>
                                    </div>
                                    <div class="portfolio-info">
                                        <h4>Kalender Akademik</h4>
                                        <p>Informasi kalender akademik terbaru</p>
                                        <!-- Link untuk melihat atau mengunduh PDF -->
                                        <a href="{{ asset('images/kalender/kalender.pdf') }}"
                                            title="Download Kalender Akademik" class="details-link" download>
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Portfolio Section -->
        <section id="portfolio" class="portfolio section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Info</h2>
                <p>Dapatkan informasi terbaru dari kami</p>
            </div><!-- End Section Title -->

            <div class="container">

                <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">
                    <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">
                        @foreach ($brosurs as $brosur)
                            <div class="col-lg-4 col-md-6 portfolio-item isotope-item filter-app">
                                <div class="portfolio-content h-100">
                                    <img src="{{ $brosur->nama }}" class="img-fluid" alt="">
                                    <div class="portfolio-info">
                                        <p>{{ $brosur->keterangan }}</p>
                                        <a href="{{ $brosur->nama }}" title="{{ $brosur->keterangan }}"
                                            data-gallery="portfolio-gallery-app" class="glightbox preview-link"><i
                                                class="bi bi-zoom-in"></i></a>
                                        <a href="portfolio-details.html" title="More Details" class="details-link"><i
                                                class="bi bi-link-45deg"></i></a>
                                    </div>
                                </div>
                            </div><!-- End Portfolio Item -->
                        @endforeach
                    </div><!-- End Portfolio Container -->
                </div>
            </div>
        </section><!-- /Portfolio Section -->

        <footer id="footer" class="footer">
            <div class="container footer-top">
              <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                  <a href="index.html" class="d-flex align-items-center">
                    <span class="sitename">SIAP</span>
                  </a>
                  <div class="footer-contact pt-3">
                    <p>Politeknik Sawungggalih Aji</p>
                    <p>Jl. Wismo Aji No. 8 , Kutoarjo</p>
                    <p class="mt-3"><strong>Telepone:</strong> <span>08560100027000</span></p>
                    <p><strong>Email:</strong> <span>polsa@ac.id</span></p>
                  </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                  <h4>Useful Links</h4>
                  <ul>
                    <li><i class="bi bi-chevron-right"></i> <a href="#">Beranda</a></li>
                    <li><i class="bi bi-chevron-right"></i> <a href="https://polsa.ac.id/" target="_blank">Informasi Kampus</a></li>
                    <li><i class="bi bi-chevron-right"></i> <a href="https://pmb.polsa.ac.id" target="_blank">PMB</a></li>
                    <li><i class="bi bi-chevron-right"></i> <a href="https://elearning.polsa.ac.id/">E-learning</a></li>
                    <li><i class="bi bi-chevron-right"></i> <a href="https://e-journal.polsa.ac.id/">E-Journal</a></li>
                  </ul>
                </div>

                <div class="col-lg-4 col-md-12">
                    <h4>Ikuti Kami</h4>
                    <p>Ikuti kami untuk mendapatkan informasi terbaru seputar sistem informasi akademik, kalender akademik, dan pengumuman penting lainnya!</p>
                  <div class="social-links d-flex">
                    <a href=""><i class="bi bi-twitter-x"></i></a>
                    <a href=""><i class="bi bi-facebook"></i></a>
                    <a href=""><i class="bi bi-instagram"></i></a>
                    <a href=""><i class="bi bi-linkedin"></i></a>
                  </div>
                </div>

              </div>
            </div>

            <div class="container copyright text-center mt-4">
              <p>© <span>Copyright</span> <strong class="px-1 sitename">SiaP</strong> <span>All Rights Reserved</span></p>
              <div class="credits">
            </div>

          </footer>

        <!-- Scroll Top -->
        <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
                class="bi bi-arrow-up-short"></i></a>

        <!-- Vendor JS Files -->
        <script src="{{ asset('/') }}land/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('/') }}land/vendor/php-email-form/validate.js"></script>
        <script src="{{ asset('/') }}land/vendor/aos/aos.js"></script>
        <script src="{{ asset('/') }}land/vendor/glightbox/js/glightbox.min.js"></script>
        <script src="{{ asset('/') }}land/vendor/purecounter/purecounter_vanilla.js"></script>
        <script src="{{ asset('/') }}land/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
        <script src="{{ asset('/') }}land/vendor/isotope-layout/isotope.pkgd.min.js"></script>
        <script src="{{ asset('/') }}land/vendor/swiper/swiper-bundle.min.js"></script>

        <!-- Main JS File -->
        <script src="{{ asset('/') }}land/js/main.js"></script>
        <script>
            const url = "{{ asset('images/kalender/kalender.pdf') }}";

            const renderPDF = (pdfUrl) => {
                pdfjsLib.getDocument(pdfUrl).promise.then((pdfDoc) => {
                    console.log('PDF loaded');
                    pdfDoc.getPage(1).then((page) => {
                        console.log('Page loaded');

                        const scale = getResponsiveScale();
                        const viewport = page.getViewport({
                            scale: scale
                        });

                        const canvas = document.getElementById('pdf-canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({
                            canvasContext: context,
                            viewport: viewport
                        });
                    });
                }, (error) => {
                    console.error('Error loading PDF: ' + error);
                });
            };


            const getResponsiveScale = () => {
                const width = window.innerWidth;
                let scale = 1;


                if (width < 600) {
                    scale = 0.5;
                } else if (width < 1024) {
                    scale = 0.75;
                } else {
                    scale = 1;
                }

                return scale;
            };

            renderPDF(url);
        </script>

</body>

</html>
