@php
    $sekolah = \App\Models\PengaturanSekolah::first() ?? new \App\Models\PengaturanSekolah([
        'nama_sekolah' => 'MI Hidayatul Hikmah',
        'nss' => '111232040082',
        'npsn' => '60706243',
        'alamat' => 'Jl. Raya Hidayatul Hikmah No. 45, Cirebon, Jawa Barat',
        'telepon' => '0231-123456',
        'email' => 'info@mihidayatulhikmah.sch.id',
        'website' => 'mihidayatulhikmah.sch.id',
        'kepala_sekolah' => 'H. Ahmad Syarifuddin, S.Pd.I',
        'nip_kepala_sekolah' => '197508122005011002',
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LMS - {{ $sekolah->nama_sekolah }}</title>

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?2=Outfit:wght@300;400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind V4 via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .bg-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(0, 0, 0, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
        }
        .dark .bg-grid {
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        }
        .blob {
            filter: blur(80px);
            opacity: 0.15;
            transition: all 1s ease;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col overflow-x-hidden transition-colors duration-300">
    
    <!-- Decorative Glowing Blobs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-400 dark:bg-emerald-600 rounded-full blob -z-10 translate-x-1/3 -translate-y-1/3"></div>
    <div class="absolute top-1/2 left-0 w-[600px] h-[600px] bg-amber-300 dark:bg-amber-600 rounded-full blob -z-10 -translate-x-1/3"></div>
    
    <!-- Navbar / Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-zinc-950/70 border-b border-slate-200/80 dark:border-zinc-800/80 transition-all">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Logo & Brand Name -->
            <a href="#" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-lg leading-tight block text-emerald-700 dark:text-emerald-400 font-display">LMS</span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider uppercase">{{ $sekolah->nama_sekolah }}</span>
                </div>
            </a>

            <!-- Quick Navigation & Dark Mode Toggle -->
            <div class="flex items-center gap-6">
                <a href="#about" class="text-sm font-medium text-slate-600 hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-400 transition-colors hidden md:inline-block">Tentang Madrasah</a>
                <a href="#portal" class="text-sm font-medium text-slate-600 hover:text-emerald-600 dark:text-slate-300 dark:hover:text-emerald-400 transition-colors hidden md:inline-block">Akses Portal</a>
                
                <a href="/admin" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 transition-all">
                    Login Admin
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="relative pt-12 pb-20 md:pt-20 md:pb-32 bg-grid">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Side: Hero Info -->
                <div class="lg:col-span-7 text-center lg:text-left space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100/80 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-bold tracking-wide uppercase">Learning Management System Aktif</span>
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-none text-slate-900 dark:text-white">
                        Membentuk Generasi <br class="hidden md:inline">
                        <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent dark:from-emerald-400 dark:to-teal-300">Cerdas & Berakhlak Mulia</span>
                    </h1>
                    
                    <p class="text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto lg:mx-0">
                        Selamat datang di Platform Pembelajaran Digital Resmi <strong class="text-emerald-700 dark:text-emerald-400 font-semibold">{{ $sekolah->nama_sekolah }}</strong>. Kami menghadirkan integrasi akademik modern untuk mendukung kemajuan siswa, transparansi wali murid, dan produktivitas pendidik.
                    </p>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#portal" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-semibold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all duration-300 text-center flex items-center justify-center gap-2 group">
                            <span>Akses Portal Utama</span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="#about" class="px-8 py-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-850 font-semibold transition-all text-center">
                            Profil Madrasah
                        </a>
                    </div>
                </div>

                <!-- Right Side: Graphic UI Preview Card -->
                <div class="lg:col-span-5 relative flex justify-center">
                    <div class="relative w-full max-w-[420px] aspect-[4/5] rounded-3xl p-6 bg-gradient-to-tr from-emerald-800 to-teal-900 text-white shadow-2xl overflow-hidden flex flex-col justify-between group hover:scale-[1.01] transition-transform duration-500 border border-emerald-500/20">
                        <!-- Abstract Design Lines inside card -->
                        <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-emerald-500/10 border border-emerald-500/20 -z-0"></div>
                        <div class="absolute -left-10 -top-10 w-40 h-40 rounded-full bg-teal-400/10 border border-teal-400/20 -z-0"></div>
                        
                        <!-- Top Header info -->
                        <div class="relative z-10 flex items-center justify-between border-b border-white/10 pb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur">
                                    <svg class="w-5 h-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm block">MI Hidayatul Hikmah</h4>
                                    <span class="text-[10px] text-emerald-200/80 tracking-wide">NPSN: {{ $sekolah->npsn }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-teal-400/20 border border-teal-400/30 text-teal-300 rounded-md text-[10px] font-bold uppercase tracking-wider">Terakreditasi A</span>
                        </div>

                        <!-- Central Mock Widget Area -->
                        <div class="relative z-10 my-6 space-y-4">
                            <!-- Feature Tag Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-2.5 backdrop-blur-sm">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-400/20 text-emerald-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                    </div>
                                    <span class="text-xs font-semibold">Tugas Digital</span>
                                </div>
                                <div class="p-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-2.5 backdrop-blur-sm">
                                    <div class="w-7 h-7 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <span class="text-xs font-semibold">Jadwal Kelas</span>
                                </div>
                                <div class="p-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-2.5 backdrop-blur-sm">
                                    <div class="w-7 h-7 rounded-lg bg-sky-400/20 text-sky-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <span class="text-xs font-semibold">Presensi Real-time</span>
                                </div>
                                <div class="p-3 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-2.5 backdrop-blur-sm">
                                    <div class="w-7 h-7 rounded-lg bg-purple-400/20 text-purple-300 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <span class="text-xs font-semibold">Rapor Online</span>
                                </div>
                            </div>

                            <!-- Mock Notification Box -->
                            <div class="p-4 bg-emerald-950/40 border border-emerald-500/30 rounded-2xl backdrop-blur">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-300 shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-white mb-0.5">Pengumuman Terbaru</h5>
                                        <p class="text-[10px] text-emerald-200/90 leading-snug">Jadwal pembagian Rapor Ujian Akhir Semester Genap dapat diakses via portal.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="relative z-10 border-t border-white/10 pt-4 flex items-center justify-between">
                            <span class="text-[10px] text-emerald-200/60 uppercase tracking-widest font-semibold">Integrated Platform</span>
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-400 border-2 border-emerald-800 flex items-center justify-center text-[9px] font-bold text-emerald-950">W</div>
                                <div class="w-6 h-6 rounded-full bg-amber-400 border-2 border-emerald-800 flex items-center justify-center text-[9px] font-bold text-emerald-950">G</div>
                                <div class="w-6 h-6 rounded-full bg-sky-400 border-2 border-emerald-800 flex items-center justify-center text-[9px] font-bold text-emerald-950">S</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Access Portals Grid -->
        <section id="portal" class="py-20 md:py-28 bg-white dark:bg-zinc-900 border-y border-slate-200 dark:border-zinc-800 transition-colors">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Pilih Portal Akses Anda
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300">
                        Gunakan portal terintegrasi sesuai dengan hak akses Anda untuk mulai mengelola atau melihat data akademik.
                    </p>
                </div>

                <!-- Three Main Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Portal Wali Murid & Siswa -->
                    <div class="group relative rounded-3xl p-8 bg-slate-50 hover:bg-slate-100/70 dark:bg-zinc-950 dark:hover:bg-zinc-850 border border-slate-200/60 dark:border-zinc-800/60 hover:border-emerald-200 dark:hover:border-emerald-900 transition-all duration-300 flex flex-col justify-between hover:shadow-xl hover:shadow-slate-100/50 dark:hover:shadow-none">
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Portal Siswa & Wali</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-450 leading-relaxed">
                                    Akses khusus untuk Wali Murid dan Siswa. Lihat presensi harian, materi belajar, pengerjaan tugas daring, dan hasil nilai rapor berkala secara langsung.
                                </p>
                            </div>
                        </div>
                        <div class="pt-8">
                            <a href="/portal" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-600 dark:text-emerald-400 group-hover:underline">
                                <span>Masuk Portal Siswa/Wali</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Portal Guru -->
                    <div class="group relative rounded-3xl p-8 bg-slate-50 hover:bg-slate-100/70 dark:bg-zinc-950 dark:hover:bg-zinc-850 border border-slate-200/60 dark:border-zinc-800/60 hover:border-amber-200 dark:hover:border-amber-900 transition-all duration-300 flex flex-col justify-between hover:shadow-xl hover:shadow-slate-100/50 dark:hover:shadow-none">
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Panel Guru & Pendidik</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-450 leading-relaxed">
                                    Akses khusus untuk Tenaga Pendidik. Kelola jadwal mengajar kelas, catat absensi kehadiran siswa, buat modul materi & penugasan, serta input penilaian rapot.
                                </p>
                            </div>
                        </div>
                        <div class="pt-8">
                            <a href="/guru" class="inline-flex items-center gap-2 text-sm font-bold text-amber-650 dark:text-amber-450 group-hover:underline">
                                <span>Masuk Panel Guru</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Portal Admin -->
                    <div class="group relative rounded-3xl p-8 bg-slate-50 hover:bg-slate-100/70 dark:bg-zinc-950 dark:hover:bg-zinc-850 border border-slate-200/60 dark:border-zinc-800/60 hover:border-slate-300 dark:hover:border-zinc-700 transition-all duration-300 flex flex-col justify-between hover:shadow-xl hover:shadow-slate-100/50 dark:hover:shadow-none">
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-300">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Portal Administrasi (LMS)</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-450 leading-relaxed">
                                    Akses khusus untuk Staff TU, Operator, dan Pimpinan. Konfigurasi data master seperti data guru & siswa, penetapan kelas, tahun ajaran baru, dan manajemen akses.
                                </p>
                            </div>
                        </div>
                        <div class="pt-8">
                            <a href="/admin" class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:underline">
                                <span>Masuk Panel Admin</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 md:py-28 bg-slate-50 dark:bg-zinc-950 transition-colors">
            <div class="max-w-7xl mx-auto px-6 space-y-16">
                <!-- Grid: Visi & Misi / Profile -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- School Identity card (School Profile info) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-md border border-slate-200/50 dark:border-zinc-800/50 flex flex-col justify-between space-y-8">
                        <div class="space-y-6">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-display border-b border-slate-100 dark:border-zinc-800 pb-4">
                                Profil Madrasah
                            </h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Lembaga</dt>
                                    <dd class="mt-1 font-bold text-slate-800 dark:text-slate-200">{{ $sekolah->nama_sekolah }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Akreditasi</dt>
                                    <dd class="mt-1 font-bold text-emerald-600 dark:text-emerald-400">A (Sangat Baik)</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NPSN</dt>
                                    <dd class="mt-1 font-mono font-medium text-slate-850 dark:text-slate-200">{{ $sekolah->npsn }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NSS</dt>
                                    <dd class="mt-1 font-mono font-medium text-slate-850 dark:text-slate-200">{{ $sekolah->nss }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kepala Madrasah</dt>
                                    <dd class="mt-1 font-semibold text-slate-855 dark:text-slate-200">{{ $sekolah->kepala_sekolah }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NIP</dt>
                                    <dd class="mt-1 font-mono text-slate-800 dark:text-slate-250">{{ $sekolah->nip_kepala_sekolah ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div class="flex items-center gap-4 bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/10 rounded-2xl p-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="text-xs">
                                <span class="font-bold block text-slate-900 dark:text-white mb-0.5">Alamat Madrasah</span>
                                <span class="text-slate-600 dark:text-slate-400">{{ $sekolah->alamat }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visi Misi Card -->
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-md border border-slate-200/50 dark:border-zinc-800/50 space-y-6">
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-display border-b border-slate-100 dark:border-zinc-800 pb-4">
                            Visi & Misi
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Visi</span>
                                <p class="text-base font-semibold leading-relaxed text-slate-800 dark:text-slate-200 italic">
                                    "Terwujudnya generasi Islam yang Qur'ani, berilmu amaliyah, beramal ilmiah, berakhlak mulia, unggul dalam mutu serta bertaqwa."
                                </p>
                            </div>
                            
                            <div class="space-y-2 pt-4">
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest block">Misi</span>
                                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                                    <li class="flex items-start gap-3">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                                        <span>Menanamkan nilai-nilai keagamaan dan pembiasaan akhlakul karimah dalam kehidupan sehari-hari siswa.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                                        <span>Menyelenggarakan kegiatan pembelajaran terintegrasi digital untuk meningkatkan mutu keilmuan siswa.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                                        <span>Membina kemandirian dan kreativitas siswa melalui pendampingan bakat minat terstruktur.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center border border-slate-200/40 dark:border-zinc-800/40 shadow-sm transition-transform hover:scale-[1.02]">
                        <span class="text-3xl md:text-4xl font-extrabold text-emerald-600 dark:text-emerald-400 font-display block">450+</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mt-2 tracking-wider">Siswa Aktif</span>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center border border-slate-200/40 dark:border-zinc-800/40 shadow-sm transition-transform hover:scale-[1.02]">
                        <span class="text-3xl md:text-4xl font-extrabold text-teal-650 dark:text-teal-400 font-display block">24</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mt-2 tracking-wider">Guru & Pendidik</span>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center border border-slate-200/40 dark:border-zinc-800/40 shadow-sm transition-transform hover:scale-[1.02]">
                        <span class="text-3xl md:text-4xl font-extrabold text-amber-600 dark:text-amber-400 font-display block">12</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mt-2 tracking-wider">Rombel Kelas</span>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 text-center border border-slate-200/40 dark:border-zinc-800/40 shadow-sm transition-transform hover:scale-[1.02]">
                        <span class="text-3xl md:text-4xl font-extrabold text-slate-700 dark:text-slate-350 font-display block">100%</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mt-2 tracking-wider">Akreditasi A</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-16 dark:bg-zinc-950 border-t border-slate-800 dark:border-zinc-900 transition-colors">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-12 gap-12 pb-12 border-b border-slate-800">
            <!-- Left Info column -->
            <div class="md:col-span-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    <span class="font-bold text-white tracking-wide text-lg font-display">{{ $sekolah->nama_sekolah }}</span>
                </div>
                <p class="text-sm leading-relaxed max-w-md">
                    Mengintegrasikan pendidikan dasar berbasis Islam dengan teknologi digital yang menunjang interaksi efektif antara madrasah, guru, siswa, dan orang tua.
                </p>
                <div class="text-xs text-slate-500">
                    &copy; 2026 {{ $sekolah->nama_sekolah }}. All rights reserved.
                </div>
            </div>

            <!-- Contacts Info column -->
            <div class="md:col-span-6 space-y-4">
                <h4 class="text-white font-bold text-sm uppercase tracking-widest font-display">Kontak & Informasi</h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>{{ $sekolah->telepon }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $sekolah->email }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <a href="https://{{ $sekolah->website }}" target="_blank" class="hover:text-white hover:underline transition-colors">{{ $sekolah->website }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

</body>
</html>
