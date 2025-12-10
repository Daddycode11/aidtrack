<?php require_once 'helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>AIDTRACK - Financial Assistance Monitoring System</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * { font-family: 'Poppins', sans-serif !important; }

        /* Loading Screen */
        #loader {
            position: fixed;
            inset: 0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #e0e0e0;
            border-top-color: #FF8A00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .glass {
            backdrop-filter: blur(12px);
            background: rgba(255,255,255,0.15);
        }

        /* Bounce + Fade animation for hero button */
        @keyframes bounceFade {
            0% { opacity: 0; transform: translateY(20px); }
            60% { opacity: 1; transform: translateY(-10px); }
            80% { transform: translateY(5px); }
            100% { transform: translateY(0); }
        }

        .animate-bounce-fade {
            animation: bounceFade 1s ease forwards;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800" x-data="{ openRegister:false }">

    <!-- LOADING SCREEN -->
    <div id="loader">
        <div class="spinner"></div>
    </div>

    <script>
        window.addEventListener("load", () => {
            const loader = document.getElementById('loader');
            loader.style.opacity = 0;
            setTimeout(() => loader.style.display = 'none', 500);
        });
    </script>

    <!-- HEADER -->
    <header class="w-full py-4 bg-white/80 backdrop-blur shadow fixed top-0 z-50" x-data="{ nav:false }">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-6">
            <a href="index.php" class="flex items-center gap-2">
                <img src="assets/images/AIDTRACK-logo.png" alt="AIDTRACK Logo" class="h-10">
            </a>

            <nav class="hidden md:flex items-center space-x-6">
                <a href="#about" class="hover:text-[#FF8A00]">About</a>
                <a href="#features" class="hover:text-[#FF8A00]">Features</a>
                <a href="#testimonials" class="hover:text-[#FF8A00]">Testimonials</a>

                <a href="login.php"
                   class="flex items-center gap-2 bg-[#FF8A00] text-white px-4 py-2 rounded-lg hover:bg-[#e07a00] shadow">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Login
                </a>

                <a href="register.php"
                   class="flex items-center gap-2 bg-white text-[#FF8A00] border border-[#FF8A00] px-4 py-2 rounded-lg hover:bg-orange-50">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Register
                </a>
            </nav>

            <button @click="nav = !nav" class="md:hidden p-2 rounded-lg border border-gray-300 hover:bg-gray-100">
                <i data-lucide="menu" class="w-6 h-6" x-show="!nav" x-cloak></i>
                <i data-lucide="x" class="w-6 h-6" x-show="nav" x-cloak></i>
            </button>
        </div>

        <nav x-show="nav" x-transition class="md:hidden bg-white/95 backdrop-blur border-t border-gray-200 shadow-lg">
            <ul class="flex flex-col text-center py-4 space-y-2">
                <li><a @click="nav=false" href="#about" class="block py-2 hover:text-[#FF8A00]">About</a></li>
                <li><a @click="nav=false" href="#features" class="block py-2 hover:text-[#FF8A00]">Features</a></li>
                <li><a @click="nav=false" href="#testimonials" class="block py-2 hover:text-[#FF8A00]">Testimonials</a></li>
                <li class="pt-2">
                    <a @click="nav=false" href="login.php"
                       class="inline-flex justify-center items-center gap-2 bg-[#FF8A00] text-white w-[60%] py-2 rounded-lg hover:bg-[#e07a00] shadow">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Login
                    </a>
                </li>
                <li>
                    <a @click="nav=false" href="register.php"
                       class="inline-flex justify-center items-center gap-2 bg-white text-[#FF8A00] border border-[#FF8A00] w-[60%] py-2 rounded-lg hover:bg-orange-50">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Register
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="h-20"></div>

    <!-- HERO SECTION -->
    <section class="relative w-full">
        <img src="assets/images/BG.png" class="w-full h-[550px] object-cover brightness-50">

        <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
            <h2 data-aos="fade-up" class="text-5xl font-bold text-white drop-shadow-xl">
                Financial Assistance Monitoring, Simplified.
            </h2>

            <p data-aos="fade-up" data-aos-delay="200"
                class="mt-4 text-lg text-gray-200 max-w-2xl mx-auto">
                AIDTRACK helps Congressman Tarriela’s office manage, track, and streamline financial assistance requests efficiently.
            </p>

            <a href="login.php"
                data-aos="fade-up" data-aos-delay="400"
                class="mt-8 inline-flex items-center gap-3 bg-[#FF8A00] text-white px-10 py-3 rounded-xl text-lg hover:bg-[#e07a00] shadow-xl transition-all duration-500 animate-bounce-fade">
                <i data-lucide="arrow-right-circle" class="w-5 h-5"></i> Get Started
            </a>
        </div>
    </section>

    <!-- ANIMATED COUNTERS -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 text-center gap-10">
            <div data-aos="fade-up">
                <h1 class="text-5xl font-bold text-[#FF8A00] counter" data-target="1200">0</h1>
                <p class="mt-2 text-gray-600">Beneficiaries Served</p>
            </div>

            <div data-aos="fade-up" data-aos-delay="200">
                <h1 class="text-5xl font-bold text-[#FF8A00] counter" data-target="350">0</h1>
                <p class="mt-2 text-gray-600">Requests Approved</p>
            </div>

            <div data-aos="fade-up" data-aos-delay="400">
                <h1 class="text-5xl font-bold text-[#FF8A00] counter" data-target="98">0</h1>
                <p class="mt-2 text-gray-600">Percent Satisfaction</p>
            </div>
        </div>
    </section>

    <script>
        // Smooth Counter Animation with subtle bounce
        document.querySelectorAll('.counter').forEach(counter => {
            const target = +counter.dataset.target;
            let count = 0;
            const step = Math.ceil(target / 200);
            const update = () => {
                count += step;
                if(count >= target) count = target;
                counter.textContent = count;
                counter.style.transform = `scale(${1 + 0.05 * Math.sin(count)})`;
                if(count < target) requestAnimationFrame(update);
            };
            update();
        });
    </script>

    <!-- FEATURES -->
    <section id="features" class="py-24 bg-gradient-to-b from-white to-orange-50">
        <div class="max-w-7xl mx-auto px-6">
            <h3 data-aos="fade-up" class="text-3xl font-semibold text-center">System Features</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mt-16">

                <div data-aos="fade-up" class="p-8 glass rounded-2xl shadow-lg">
                    <i data-lucide="layout-dashboard" class="w-10 h-10 text-[#FF8A00] mb-4"></i>
                    <h4 class="text-xl font-bold text-orange-700">Admin Dashboard</h4>
                    <p class="text-gray-700 mt-2">Monitor requests, beneficiaries, and reports.</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="200" class="p-8 glass rounded-2xl shadow-lg">
                    <i data-lucide="clipboard-list" class="w-10 h-10 text-[#FF8A00] mb-4"></i>
                    <h4 class="text-xl font-bold text-orange-700">Request Tracking</h4>
                    <p class="text-gray-700 mt-2">Track and validate financial assistance requests.</p>
                </div>

                <div data-aos="fade-up" data-aos-delay="400" class="p-8 glass rounded-2xl shadow-lg">
                    <i data-lucide="folder-lock" class="w-10 h-10 text-[#FF8A00] mb-4"></i>
                    <h4 class="text-xl font-bold text-orange-700">Document Management</h4>
                    <p class="text-gray-700 mt-2">Secure upload and verification of documents.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section id="testimonials" class="py-24 bg-white">
        <h3 data-aos="fade-up" class="text-3xl font-semibold text-center">What People Say</h3>

        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 mt-16 px-6">

            <div data-aos="fade-up" class="p-8 rounded-xl shadow bg-gray-50">
                <p class="text-gray-600 italic">“Napadali ang proseso, mabilis at maayos!”</p>
                <h4 class="mt-4 font-semibold text-[#FF8A00]">– Resident, Calintaan</h4>
            </div>

            <div data-aos="fade-up" data-aos-delay="200" class="p-8 rounded-xl shadow bg-gray-50">
                <p class="text-gray-600 italic">“Mas transparent at mas organized ang distribution.”</p>
                <h4 class="mt-4 font-semibold text-[#FF8A00]">– Barangay Staff</h4>
            </div>

            <div data-aos="fade-up" data-aos-delay="400" class="p-8 rounded-xl shadow bg-gray-50">
                <p class="text-gray-600 italic">“Big help sa monitoring at reporting.”</p>
                <h4 class="mt-4 font-semibold text-[#FF8A00]">– Office Personnel</h4>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-6 bg-gray-900 text-gray-300 text-center">
        <p class="text-sm">© <?=date('Y')?> AIDTRACK | Financial Assistance Monitoring System</p>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1200, once:true });
        lucide.createIcons();
    </script>

</body>
</html>
