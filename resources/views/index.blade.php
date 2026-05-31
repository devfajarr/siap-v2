<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>SIAP POLSA - Sistem Informasi Akademik</title>
    
    <!-- Favicon -->
    <link href="{{ asset('/images/logomini.png') }}" rel="icon">
    
    <!-- Fonts & Icons -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-container": "#c8c6ff",
                        "on-tertiary-container": "#ffbab4",
                        "tertiary-container": "#af0019",
                        "background": "#faf8ff",
                        "on-error": "#ffffff",
                        "surface": "#faf8ff",
                        "surface-dim": "#d2d9f4",
                        "surface-container-high": "#e2e7ff",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#ffb3ad",
                        "secondary-fixed-dim": "#c0c1ff",
                        "surface-bright": "#faf8ff",
                        "on-primary-fixed": "#0d006a",
                        "primary-fixed": "#e2dfff",
                        "error": "#ba1a1a",
                        "outline": "#777583",
                        "primary-fixed-dim": "#c2c1ff",
                        "on-tertiary-fixed-variant": "#930013",
                        "tertiary-fixed": "#ffdad7",
                        "surface-container-lowest": "#ffffff",
                        "inverse-surface": "#283044",
                        "surface-variant": "#dae2fd",
                        "secondary-container": "#6063ee",
                        "primary": "#333093",
                        "on-secondary-fixed-variant": "#2f2ebe",
                        "on-secondary-fixed": "#07006c",
                        "on-surface-variant": "#464552",
                        "secondary-fixed": "#e1e0ff",
                        "primary-container": "#4b49ac",
                        "surface-container-low": "#f2f3ff",
                        "surface-container-highest": "#dae2fd",
                        "on-secondary": "#ffffff",
                        "secondary": "#4648d4",
                        "on-tertiary-fixed": "#410004",
                        "outline-variant": "#c7c5d4",
                        "on-background": "#131b2e",
                        "surface-tint": "#5452b5",
                        "on-primary": "#ffffff",
                        "on-error-container": "#93000a",
                        "tertiary": "#83000f",
                        "on-primary-fixed-variant": "#3b399c",
                        "error-container": "#ffdad6",
                        "surface-container": "#eaedff",
                        "inverse-primary": "#c2c1ff",
                        "on-surface": "#131b2e",
                        "inverse-on-surface": "#eef0ff"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    spacing: {
                        gutter: "24px",
                        sm: "16px",
                        md: "24px",
                        "container-max": "1280px",
                        xl: "64px",
                        base: "4px",
                        xs: "8px",
                        lg: "40px"
                    },
                    fontFamily: {
                        "display-lg": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "title-md": ["Plus Jakarta Sans"],
                        "body-md": ["Nunito Sans"],
                        "label-sm": ["Nunito Sans"],
                        "body-lg": ["Nunito Sans"],
                        "headline-lg-mobile": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "display-lg": ["48px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-lg": ["32px", { lineHeight: "1.3", fontWeight: "700" }],
                        "title-md": ["20px", { lineHeight: "1.4", fontWeight: "600" }],
                        "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                        "label-sm": ["12px", { lineHeight: "1", letterSpacing: "0.05em", fontWeight: "700" }],
                        "body-lg": ["18px", { lineHeight: "1.6", fontWeight: "400" }],
                        "headline-lg-mobile": ["24px", { lineHeight: "1.3", fontWeight: "700" }]
                    }
                }
            }
        }
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased overflow-x-hidden selection:bg-primary selection:text-on-primary">

    <!-- Section 1: Sticky Header (TopNavBar variant) -->
    <header class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-xl border-b border-gray-100 transition-all duration-300" style="box-shadow: 0 2px 20px rgba(75,73,172,0.08);">
        <div class="flex justify-between items-center w-full px-gutter max-w-container-max mx-auto" style="height: 72px;">
            <!-- Brand Logo -->
            <a class="flex items-center gap-xs shrink-0" href="/">
                <img alt="SIAP POLSA Logo" class="h-10 w-auto" src="{{ asset('images/logo1.png') }}">
            </a>
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-2">
                <a class="relative px-5 py-2 text-[15px] font-bold text-[#4B49AC] rounded-xl transition-all duration-200" href="/"
                   style="background: rgba(75,73,172,0.08);">
                    Beranda
                </a>
                <a class="relative px-5 py-2 text-[15px] font-semibold text-gray-600 rounded-xl hover:text-[#4B49AC] hover:bg-[#4B49AC]/8 transition-all duration-200" href="#kalender-akademik">
                    Kalender
                </a>
                <a class="relative px-5 py-2 text-[15px] font-semibold text-gray-600 rounded-xl hover:text-[#4B49AC] hover:bg-[#4B49AC]/8 transition-all duration-200" href="#brosur">
                    Pengumuman
                </a>
            </nav>
            <!-- Actions -->
            <div class="flex items-center gap-sm">
                <a href="/login" class="hidden md:flex items-center gap-xs px-6 py-2.5 text-white rounded-xl font-bold text-[14px] transition-all duration-200 active:scale-95"
                   style="background: #4B49AC; box-shadow: 0 4px 14px rgba(75,73,172,0.35);"
                   onmouseover="this.style.background='#3b398c'; this.style.boxShadow='0 6px 20px rgba(75,73,172,0.45)'"
                   onmouseout="this.style.background='#4B49AC'; this.style.boxShadow='0 4px 14px rgba(75,73,172,0.35)'">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    <span>Login</span>
                </a>
                <button id="mobile-menu-btn" class="md:hidden text-[#4B49AC] p-2 hover:bg-[#4B49AC]/10 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Menu -->
    <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/98 backdrop-blur-md pt-24 px-gutter flex flex-col gap-sm">
        <a class="flex items-center gap-sm text-[16px] font-bold text-[#4B49AC] py-4 px-4 rounded-xl" href="/"
           style="background: rgba(75,73,172,0.08);">
            <span class="material-symbols-outlined text-xl">home</span>
            Beranda
        </a>
        <a class="flex items-center gap-sm text-[16px] font-semibold text-gray-600 hover:text-[#4B49AC] py-4 px-4 rounded-xl hover:bg-[#4B49AC]/8 transition-all" href="#kalender-akademik">
            <span class="material-symbols-outlined text-xl">calendar_month</span>
            Kalender
        </a>
        <a class="flex items-center gap-sm text-[16px] font-semibold text-gray-600 hover:text-[#4B49AC] py-4 px-4 rounded-xl hover:bg-[#4B49AC]/8 transition-all" href="#brosur">
            <span class="material-symbols-outlined text-xl">campaign</span>
            Pengumuman
        </a>
        <div class="mt-auto mb-8">
            <a href="/login" class="w-full flex items-center justify-center gap-xs py-4 text-white rounded-xl font-bold text-[16px] transition-all"
               style="background: #4B49AC; box-shadow: 0 4px 14px rgba(75,73,172,0.35);">
                <span class="material-symbols-outlined">login</span>
                Login ke SIAP
            </a>
        </div>
    </div>


    <!-- Section 2: Hero Section -->
    <section class="w-full pt-32 pb-xl" style="background: #ffffff;">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="flex flex-col md:flex-row items-center justify-between gap-xl">
                <!-- Text Content -->
                <div class="flex-1 flex flex-col gap-md text-center md:text-left">
                    <h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-display-lg md:text-display-lg text-[#4B49AC] tracking-tight font-bold">
                        Sistem Informasi Akademik Polsa
                    </h1>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto md:mx-0">
                        Layanan informasi terintegrasi untuk mahasiswa, dosen, dan administrasi Politeknik Sawunggalih Aji. Tingkatkan efisiensi dan pengalaman akademik Anda bersama kami.
                    </p>
                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row gap-sm mt-sm">
                        <a href="/login?role=mahasiswa" class="w-full sm:w-auto flex items-center justify-center gap-xs px-8 py-4 bg-[#4B49AC] text-white rounded-xl hover:bg-[#3b398c] transition-colors shadow-lg shadow-[#4B49AC]/30 active:scale-95 min-h-[44px]">
                            <span class="material-symbols-outlined text-xl">person</span>
                            <span class="font-title-md text-body-md font-bold">Login Sebagai Mahasiswa</span>
                        </a>
                        <a href="/login" class="w-full sm:w-auto flex items-center justify-center gap-xs px-8 py-4 border-2 border-[#4B49AC] text-[#4B49AC] rounded-xl hover:bg-[#4B49AC]/5 transition-colors active:scale-95 min-h-[44px]">
                            <span class="font-title-md text-body-md font-bold">Login Umum</span>
                            <span class="material-symbols-outlined text-xl">arrow_forward</span>
                        </a>
                    </div>
                </div>
                <!-- Illustration -->
                <div class="flex-1 w-full flex justify-center">
                    <img alt="SIAP POLSA Academic Platform Illustration" class="w-full max-w-md md:max-w-xl object-contain" src="{{ asset('images/hero-img.png') }}">
                </div>
            </div>
        </div>
    </section>


    <!-- Section 4: Academic Calendar & Announcements -->
    <section id="kalender-akademik" class="py-xl px-gutter max-w-container-max mx-auto relative">
        <div class="text-center mb-lg">
            <span class="inline-block px-3 py-1 bg-primary/10 text-[#4B49AC] font-label-sm text-label-sm rounded-full mb-4">KALENDER AKADEMIK</span>
            <h2 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-on-background font-bold">Informasi terbaru mengenai jadwal akademik</h2>
        </div>
        <div class="flex flex-col md:flex-row gap-lg items-start">
            <!-- Document Mockup Card (PDF Canvas Viewer) -->
            <div class="w-full md:w-2/3 bg-white rounded-2xl shadow-2xl border border-outline-variant/30 p-4 md:p-8 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-white/50 to-transparent pointer-events-none z-10"></div>
                <!-- PDF Viewer Canvas -->
                <div id="pdf-container" class="w-full h-auto overflow-hidden flex justify-center bg-white rounded-lg shadow-inner min-h-[300px] md:min-h-[500px]">
                    <canvas id="pdf-canvas" class="w-full h-auto block rounded-lg shadow-md"></canvas>
                </div>
                <!-- Overlay for aesthetics -->
                <div class="absolute bottom-0 left-0 w-full p-6 bg-gradient-to-t from-white via-white/80 to-transparent z-20 flex justify-between items-end">
                    <div>
                        <h4 class="font-title-md text-title-md text-on-surface font-bold">Kalender Akademik</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Informasi kalender akademik terbaru</p>
                    </div>
                </div>
            </div>
            <!-- Contextual Info / Actions -->
            <div class="w-full md:w-1/3 flex flex-col gap-md sticky top-24">
                <div class="bg-amber-50 p-6 rounded-xl border border-amber-200 shadow-sm">
                    <h3 class="font-title-md text-title-md text-amber-700 mb-2 flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-amber-600">campaign</span>
                        Penting
                    </h3>
                    <p class="font-body-md text-body-md text-amber-900/80">
                        Pastikan Anda telah menyelesaikan administrasi pembayaran sebelum melakukan pengisian Kartu Rencana Studi (KRS).
                    </p>
                </div>
                <a href="{{ asset('images/kalender/kalender.pdf') }}" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-[#4B49AC] text-white rounded-xl shadow-lg shadow-[#4B49AC]/20 hover:bg-[#3b398c] transition-all active:scale-95" download>
                    <span class="material-symbols-outlined">download</span>
                    <span class="font-title-md text-body-md font-bold">Unduh PDF Kalender</span>
                </a>
            </div>
        </div>
    </section>



    <!-- Section 6: Brosur & Informasi Tambahan -->
    <section id="brosur" class="py-xl bg-surface-container-low px-gutter border-t border-surface-container-highest">
        <div class="max-w-container-max mx-auto">
            <div class="text-center mb-lg">
                <span class="inline-block px-3 py-1 bg-primary/10 text-[#4B49AC] font-label-sm text-label-sm rounded-full mb-4">PENGUMUMAN & BROSUR</span>
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-on-background font-bold">Dapatkan informasi terbaru dari kami</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                @foreach ($brosurs as $brosur)
                <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden group flex-1 bg-surface-container-high aspect-[4/3] flex items-center justify-center">
                        <img src="{{ $brosur->nama }}" alt="{{ $brosur->keterangan }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="p-6">
                        <p class="font-body-md text-on-surface-variant line-clamp-3">{{ $brosur->keterangan }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Section 7: Footer -->
    <footer class="bg-surface-container-low dark:bg-surface-container-lowest w-full py-lg border-t border-outline-variant">
        <div class="max-w-container-max mx-auto px-gutter">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-xl mb-xl">
                <!-- Brand Info -->
                <div class="md:col-span-4 flex flex-col gap-4">
                    <img alt="SIAP POLSA Logo" class="h-10 w-auto object-contain self-start" src="{{ asset('images/logo1.png') }}">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Politeknik Sawunggalih Aji<br>
                        Jl. Wismo Aji No. 8, Kutoarjo
                    </p>
                    <div class="font-body-md text-body-md text-on-surface-variant mt-2">
                        <p class=""><strong>Telepon:</strong> 085601000270</p>
                        <p class=""><strong>Email:</strong> polsa@ac.id</p>
                    </div>
                </div>
                <!-- Useful Links -->
                <div class="md:col-span-3">
                    <h4 class="font-title-md text-title-md font-bold text-[#4B49AC] mb-6">Useful Links</h4>
                    <ul class="flex flex-col gap-3">
                        <li class=""><a class="font-body-md text-body-md text-on-surface-variant hover:text-[#4B49AC] hover:underline transition-all flex items-center gap-2" href="/"><span class="material-symbols-outlined text-sm">chevron_right</span>Beranda</a></li>
                        <li class=""><a class="font-body-md text-body-md text-on-surface-variant hover:text-[#4B49AC] hover:underline transition-all flex items-center gap-2" href="https://polsa.ac.id/" target="_blank"><span class="material-symbols-outlined text-sm">chevron_right</span>Informasi Kampus</a></li>
                        <li class=""><a class="font-body-md text-body-md text-on-surface-variant hover:text-[#4B49AC] hover:underline transition-all flex items-center gap-2" href="https://pmb.polsa.ac.id" target="_blank"><span class="material-symbols-outlined text-sm">chevron_right</span>PMB</a></li>
                        <li class=""><a class="font-body-md text-body-md text-on-surface-variant hover:text-[#4B49AC] hover:underline transition-all flex items-center gap-2" href="https://elearning.polsa.ac.id/" target="_blank"><span class="material-symbols-outlined text-sm">chevron_right</span>E-learning</a></li>
                        <li class=""><a class="font-body-md text-body-md text-on-surface-variant hover:text-[#4B49AC] hover:underline transition-all flex items-center gap-2" href="https://e-journal.polsa.ac.id/" target="_blank"><span class="material-symbols-outlined text-sm">chevron_right</span>E-Journal</a></li>
                    </ul>
                </div>
                <!-- Socials & Info -->
                <div class="md:col-span-5 flex flex-col gap-4">
                    <h4 class="font-title-md text-title-md font-bold text-[#4B49AC] mb-2">Ikuti Kami</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-4">
                        Ikuti kanal resmi Politeknik Sawunggalih Aji untuk mendapatkan informasi terbaru seputar kegiatan kampus, pengumuman, dan akademik.
                    </p>
                    <div class="flex gap-4 items-center">
                        <!-- Facebook -->
                        <a href="https://facebook.com/POLSAKUTOARJO" target="_blank" rel="noopener"
                           class="w-10 h-10 rounded-full bg-surface border border-outline-variant flex items-center justify-center hover:bg-blue-50 hover:border-blue-300 transition-colors" title="Facebook POLSA">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <!-- Instagram -->
                        <a href="https://www.instagram.com/politeknikkutoarjo/" target="_blank" rel="noopener"
                           class="w-10 h-10 rounded-full bg-surface border border-outline-variant flex items-center justify-center hover:bg-pink-50 hover:border-pink-300 transition-colors" title="Instagram POLSA">
                            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="ig-grad" x1="0%" y1="100%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#f09433"/>
                                        <stop offset="25%" style="stop-color:#e6683c"/>
                                        <stop offset="50%" style="stop-color:#dc2743"/>
                                        <stop offset="75%" style="stop-color:#cc2366"/>
                                        <stop offset="100%" style="stop-color:#bc1888"/>
                                    </linearGradient>
                                </defs>
                                <path fill="url(#ig-grad)" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                        </a>
                        <!-- YouTube -->
                        <a href="https://www.youtube.com/c/PoliteknikKutoarjo" target="_blank" rel="noopener"
                           class="w-10 h-10 rounded-full bg-surface border border-outline-variant flex items-center justify-center hover:bg-red-50 hover:border-red-300 transition-colors" title="YouTube POLSA">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#FF0000" xmlns="http://www.w3.org/2000/svg">
                                <path d="M23.495 6.205a3.007 3.007 0 00-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 00.527 6.205a31.247 31.247 0 00-.522 5.805 31.247 31.247 0 00.522 5.783 3.007 3.007 0 002.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 002.088-2.088 31.247 31.247 0 00.5-5.783 31.247 31.247 0 00-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Copyright -->
            <div class="pt-8 border-t border-outline-variant text-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    © 2026 SIAP POLSA. Institutional Authority Built for Excellence. All Rights Reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- FAB for Back to Top -->
    <div class="fixed bottom-8 right-8 z-50">
        <a href="#" id="scroll-top" class="w-12 h-12 bg-[#4B49AC] text-white rounded-full shadow-lg flex items-center justify-center hover:bg-[#3b398c] transition-colors active:scale-95">
            <span class="material-symbols-outlined">arrow_upward</span>
        </a>
    </div>

    <!-- Mobile Menu Toggle JavaScript -->
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileMenuBtn.querySelector('span');
            if (mobileMenu.classList.contains('hidden')) {
                icon.textContent = 'menu';
            } else {
                icon.textContent = 'close';
            }
        });

        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.querySelector('span').textContent = 'menu';
            });
        });
    </script>

    <!-- PDF Viewer Script -->
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
                scale = 1.2;
            }

            return scale;
        };

        renderPDF(url);

        // Re-render PDF on window resize to ensure responsiveness
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                renderPDF(url);
            }, 300);
        });
    </script>
</body>
</html>
