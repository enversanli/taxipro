<!DOCTYPE html>
<html lang="de" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaxiPro – Das Betriebssystem für Ihre Flotte</title>

    <!-- Fonts: Inter for a clean SaaS look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config for Custom Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Brand Color
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(37, 99, 235, 0.15)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Styles for graphical elements */
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .blob {
            position: absolute;
            filter: blur(40px);
            z-index: -1;
            opacity: 0.4;
            animation: move 10s infinite alternate;
        }
        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(20px, -20px) scale(1.1); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased overflow-x-hidden">

<!-- Decorative Background Elements -->
<div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-50 pointer-events-none">
    <div class="blob bg-blue-300 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob bg-indigo-200 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3"></div>
</div>

<!-- Navigation -->
<nav class="fixed w-full z-50 top-0 border-b border-slate-200/60 glass-nav transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center gap-2 cursor-pointer">
                <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-glow">
                    T
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900">TaxiPro</span>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Funktionen</a>
                <a href="#pricing" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Preise</a>
                <a href="#testimonials" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Kunden</a>

                <div class="flex items-center gap-4 border-l border-slate-200 pl-6">
                    <a href="#" class="text-sm font-medium text-slate-900 hover:text-primary-600">Login</a>
                    <a href="#contact" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-full text-sm font-medium transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50 hover:-translate-y-0.5">
                        Demo anfragen
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-white border-b border-slate-200 shadow-xl flex-col p-6 space-y-4 md:hidden">
        <a href="#features" class="block text-slate-600 font-medium hover:text-primary-600">Funktionen</a>
        <a href="#pricing" class="block text-slate-600 font-medium hover:text-primary-600">Preise</a>
        <hr class="border-slate-100">
        <a href="#contact" class="block w-full text-center bg-primary-600 text-white py-3 rounded-lg font-medium">Demo anfragen</a>
    </div>
</nav>

<!-- Hero Section -->
<section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

            <!-- Text Content -->
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-primary-600 text-xs font-semibold uppercase tracking-wide mb-6">
                    <span class="w-2 h-2 rounded-full bg-primary-600 animate-pulse"></span>
                    Neu: KI-Routenoptimierung
                </div>
                <h1 class="text-5xl lg:text-6xl font-extrabold text-slate-900 leading-[1.1] mb-6">
                    Das Betriebssystem für <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-indigo-600">moderne Taxi-Flotten</span>.
                </h1>
                <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                    Verabschieden Sie sich von Papierkram. TaxiPro automatisiert Ihre Abrechnung, verwaltet Fahrer und optimiert Ihre Einnahmen – alles in einer App.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#contact" class="inline-flex justify-center items-center px-8 py-4 text-base font-semibold text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-500/30 hover:-translate-y-1">
                        Kostenlos testen
                    </a>
                    <a href="#features" class="inline-flex justify-center items-center px-8 py-4 text-base font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all hover:border-slate-300">
                        <svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Video ansehen
                    </a>
                </div>

                <div class="mt-10 flex items-center gap-4 text-sm text-slate-500">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-slate-300 border-2 border-white"></div>
                        <div class="w-8 h-8 rounded-full bg-slate-400 border-2 border-white"></div>
                    </div>
                    <p>Bereits von <span class="font-bold text-slate-900">500+</span> Unternehmen genutzt</p>
                </div>
            </div>

            <!-- Abstract Dashboard Visual -->
            <div class="relative lg:h-auto">
                <div class="relative rounded-2xl bg-white border border-slate-200 shadow-2xl shadow-slate-200/50 overflow-hidden transform rotate-1 hover:rotate-0 transition duration-700 ease-out">
                    <!-- Fake Browser Header -->
                    <div class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <!-- Fake Dashboard Content -->
                    <div class="p-6 grid grid-cols-3 gap-4 bg-slate-50/50">
                        <!-- Sidebar -->
                        <div class="col-span-1 space-y-3">
                            <div class="h-8 w-3/4 bg-primary-100 rounded mb-6"></div>
                            <div class="h-4 w-full bg-slate-200 rounded"></div>
                            <div class="h-4 w-5/6 bg-slate-200 rounded"></div>
                            <div class="h-4 w-4/6 bg-slate-200 rounded"></div>
                        </div>
                        <!-- Main Area -->
                        <div class="col-span-2 space-y-4">
                            <div class="flex justify-between">
                                <div class="h-20 w-32 bg-white rounded-lg shadow-sm border border-slate-100 p-3">
                                    <div class="h-3 w-12 bg-green-100 rounded mb-2"></div>
                                    <div class="h-6 w-20 bg-slate-800 rounded"></div>
                                </div>
                                <div class="h-20 w-32 bg-white rounded-lg shadow-sm border border-slate-100 p-3">
                                    <div class="h-3 w-12 bg-blue-100 rounded mb-2"></div>
                                    <div class="h-6 w-20 bg-slate-800 rounded"></div>
                                </div>
                            </div>
                            <div class="h-40 w-full bg-white rounded-lg shadow-sm border border-slate-100 flex items-end justify-between p-4 px-6 gap-2">
                                <div class="w-full bg-blue-100 rounded-t h-[40%]"></div>
                                <div class="w-full bg-primary-600 rounded-t h-[70%]"></div>
                                <div class="w-full bg-blue-100 rounded-t h-[50%]"></div>
                                <div class="w-full bg-blue-100 rounded-t h-[60%]"></div>
                                <div class="w-full bg-blue-100 rounded-t h-[45%]"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Floating Badge -->
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-xl border border-slate-100 flex items-center gap-3 animate-bounce" style="animation-duration: 3s;">
                    <div class="p-2 bg-green-100 rounded-lg text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Umsatz heute</p>
                        <p class="text-lg font-bold text-slate-900">+1.240€</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof -->
<section class="py-10 border-y border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest mb-6">Vertraut von führenden Unternehmen</p>
        <div class="flex flex-wrap justify-center gap-8 md:gap-16 opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
            <!-- Simple Text Logos for Demo Purposes -->
            <span class="text-xl font-bold text-slate-700">UberPartner</span>
            <span class="text-xl font-bold text-slate-700">CityCab</span>
            <span class="text-xl font-bold text-slate-700">DriveNow</span>
            <span class="text-xl font-bold text-slate-700">FreeNow Flotten</span>
            <span class="text-xl font-bold text-slate-700">Bolt Connect</span>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-24 bg-slate-50 relative">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-primary-600 font-semibold tracking-wide uppercase text-sm mb-3">Features</h2>
            <h3 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Alles, was Sie für Ihre Flotte brauchen</h3>
            <p class="text-slate-600 text-lg">TaxiPro ersetzt komplexe Excel-Tabellen durch eine intuitive Oberfläche.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-primary-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-3">Smartes Fahrer-Management</h4>
                <p class="text-slate-600 leading-relaxed">Verwalten Sie P-Scheine, Schichten und Urlaube zentral. Automatische Erinnerungen bei ablaufenden Dokumenten.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 36v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-3">1-Klick Lohnabrechnung</h4>
                <p class="text-slate-600 leading-relaxed">Berechnen Sie Provisionen und Festgehälter automatisch basierend auf importierten Fahrtdaten (Uber/Bolt/FreeNow).</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-3">Finanz-Cockpit</h4>
                <p class="text-slate-600 leading-relaxed">Echtzeit-Analyse Ihrer Einnahmen, Barzahlungen und offenen Posten. Bereit für den Steuerberater exportieren.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Transparente Preise</h2>
        <p class="text-slate-600 mb-12">Wachsen Sie mit uns. Keine versteckten Kosten.</p>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto items-center">

            <!-- Starter -->
            <div class="p-8 bg-white border border-slate-200 rounded-2xl hover:border-slate-300 transition">
                <h4 class="text-xl font-bold text-slate-900 mb-2">Starter</h4>
                <p class="text-sm text-slate-500 mb-6">Für Einsteiger & kleine Teams</p>
                <div class="mb-6">
                    <span class="text-4xl font-extrabold text-slate-900">29€</span>
                    <span class="text-slate-500">/Monat</span>
                </div>
                <a href="#contact" class="block w-full py-3 px-6 bg-slate-50 text-slate-900 font-semibold rounded-lg hover:bg-slate-100 transition border border-slate-200">Starten</a>
                <ul class="mt-8 space-y-3 text-left text-sm text-slate-600">
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Bis zu 5 Fahrer</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Lohnabrechnung</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> E-Mail Support</li>
                </ul>
            </div>

            <!-- Business (Highlighted) -->
            <div class="relative p-8 bg-slate-900 rounded-2xl shadow-2xl transform md:scale-105 z-10 text-white">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wide shadow-lg">Beliebt</div>
                <h4 class="text-xl font-bold mb-2">Business</h4>
                <p class="text-sm text-slate-400 mb-6">Für wachsende Unternehmen</p>
                <div class="mb-6">
                    <span class="text-5xl font-extrabold">59€</span>
                    <span class="text-slate-400">/Monat</span>
                </div>
                <a href="#contact" class="block w-full py-3 px-6 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-500 transition shadow-lg shadow-primary-500/50">Jetzt wählen</a>
                <ul class="mt-8 space-y-3 text-left text-sm text-slate-300">
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Bis zu 20 Fahrer</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Priorisierter Support</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Erweiterte Berichte</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> DATEV Export</li>
                </ul>
            </div>

            <!-- Enterprise -->
            <div class="p-8 bg-white border border-slate-200 rounded-2xl hover:border-slate-300 transition">
                <h4 class="text-xl font-bold text-slate-900 mb-2">Enterprise</h4>
                <p class="text-sm text-slate-500 mb-6">Für Großflotten & Konzerne</p>
                <div class="mb-6">
                    <span class="text-3xl font-extrabold text-slate-900">Individuell</span>
                </div>
                <a href="#contact" class="block w-full py-3 px-6 bg-white text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition border border-slate-200">Kontakt</a>
                <ul class="mt-8 space-y-3 text-left text-sm text-slate-600">
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Unbegrenzte Fahrer</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Dedizierter Account Manager</li>
                    <li class="flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Onboarding & Schulung</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-24 bg-gradient-to-br from-primary-900 to-slate-900 text-white relative overflow-hidden">
    <!-- Decoration -->
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-primary-500 rounded-full blur-3xl opacity-20"></div>
    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>

    <div class="max-w-4xl mx-auto px-6 relative z-10 flex flex-col md:flex-row gap-12 items-center">

        <div class="md:w-1/2">
            <h3 class="text-3xl font-bold mb-4">Bereit, Ihre Flotte zu optimieren?</h3>
            <p class="text-slate-300 mb-8 text-lg">Fordern Sie jetzt Ihre kostenlose Demo an. Wir zeigen Ihnen in 15 Minuten, wie Sie bis zu 20% Zeit sparen können.</p>
            <div class="flex items-center gap-4 text-sm text-slate-300">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Keine Kreditkarte nötig
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Sofortiger Zugang
                </div>
            </div>
        </div>

        <div class="md:w-1/2 w-full bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/10">
            <form action="#" method="POST" class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-slate-300 uppercase tracking-wide">Name</label>
                    <input type="text" class="w-full mt-1 bg-slate-800/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary-500 focus:outline-none transition" placeholder="Max Mustermann">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-300 uppercase tracking-wide">E-Mail</label>
                    <input type="email" class="w-full mt-1 bg-slate-800/50 border border-slate-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary-500 focus:outline-none transition" placeholder="max@firma.de">
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-500 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-primary-500/50 mt-2">
                    Demo anfordern
                </button>
            </form>
        </div>

    </div>
</section>

<!-- Footer -->
<footer class="bg-white border-t border-slate-200 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
        <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 bg-primary-600 rounded flex items-center justify-center text-white font-bold text-xs">T</div>
                <span class="font-bold text-slate-900">TaxiPro</span>
            </div>
            <p class="text-sm text-slate-500">Made in Berlin. Wir digitalisieren das Taxigewerbe.</p>
        </div>
        <div>
            <h5 class="font-bold text-slate-900 mb-4">Produkt</h5>
            <ul class="space-y-2 text-sm text-slate-500">
                <li><a href="#" class="hover:text-primary-600">Funktionen</a></li>
                <li><a href="#" class="hover:text-primary-600">Preise</a></li>
                <li><a href="#" class="hover:text-primary-600">Changelog</a></li>
            </ul>
        </div>
        <div>
            <h5 class="font-bold text-slate-900 mb-4">Rechtliches</h5>
            <ul class="space-y-2 text-sm text-slate-500">
                <li><a href="#" class="hover:text-primary-600">Impressum</a></li>
                <li><a href="#" class="hover:text-primary-600">Datenschutz</a></li>
                <li><a href="#" class="hover:text-primary-600">AGB</a></li>
            </ul>
        </div>
        <div>
            <h5 class="font-bold text-slate-900 mb-4">Social</h5>
            <div class="flex space-x-4">
                <a href="#" class="text-slate-400 hover:text-primary-600"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
            </div>
        </div>
    </div>
    <div class="text-center text-xs text-slate-400 border-t border-slate-100 pt-8">
        <script>document.write(new Date().getFullYear())</script> TaxiPro GmbH
    </div>
</footer>

<!-- Scripts -->
<script>
    // Mobile Menu Toggle
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');

    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
    });

    // Sticky Navbar Effect
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 10) {
            navbar.classList.add('shadow-md');
            navbar.classList.replace('py-4', 'py-2');
        } else {
            navbar.classList.remove('shadow-md');
            navbar.classList.replace('py-2', 'py-4');
        }
    });
</script>
</body>
</html>
