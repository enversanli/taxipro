<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaxiPro – Moderne Software für Taxiunternehmen</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

<!-- Header -->
<header class="sticky top-0 bg-white shadow z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-blue-600">TaxiPro</h1>
        <nav class="hidden md:flex space-x-6 text-gray-700">
            <a href="#features" class="hover:text-blue-600 transition">Funktionen</a>
            <a href="#pricing" class="hover:text-blue-600 transition">Preise</a>
            <a href="#contact" class="hover:text-blue-600 transition">Kontakt</a>
        </nav>
        <a href="#contact" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            Demo anfragen
        </a>
        <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden px-6 pb-4 space-y-2">
        <a href="#features" class="block text-gray-700 hover:text-blue-600 transition">Funktionen</a>
        <a href="#pricing" class="block text-gray-700 hover:text-blue-600 transition">Preise</a>
        <a href="#contact" class="block text-gray-700 hover:text-blue-600 transition">Kontakt</a>
    </div>
</header>

<!-- Hero Section -->
<section class="bg-blue-50 py-20">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <h2 class="text-4xl md:text-5xl font-extrabold text-blue-800 mb-4">Die smarte Software für Taxiunternehmen</h2>
        <p class="text-lg md:text-xl text-gray-700 mb-8">
            Verwalten Sie Fahrer, Einnahmen, Gehälter und Buchhaltung – alles an einem Ort.
        </p>
        <a href="#contact" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
            Jetzt kostenlos testen
        </a>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6 text-center">
        <h3 class="text-3xl font-bold mb-10">Was bietet TaxiPro?</h3>
        <div class="grid gap-8 md:grid-cols-3">
            <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold text-blue-600 mb-2">Fahrer-Management</h4>
                <p class="text-gray-700">Alle Fahrerinformationen zentral speichern, inkl. Lizenzprüfungen, Arbeitszeit und mehr.</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold text-blue-600 mb-2">Automatische Gehaltsberechnung</h4>
                <p class="text-gray-700">Basierend auf Fahrten, Trinkgeldern und Pauschalen – automatisiert & transparent.</p>
            </div>
            <div class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-lg transition">
                <h4 class="text-xl font-semibold text-blue-600 mb-2">Einnahmen & Buchhaltung</h4>
                <p class="text-gray-700">Überblick über Einnahmen, Barzahlungen, Rechnungen und Monatsabschlüsse behalten.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-24 bg-gray-100">
    <div class="max-w-5xl mx-auto px-6 text-center">
        <h3 class="text-3xl font-bold mb-12">Faire & transparente Preise</h3>
        <div class="grid gap-8 md:grid-cols-3">
            <!-- Starter -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="text-xl font-semibold mb-2">Starter</h4>
                <p class="text-gray-600 mb-4">Für kleine Teams</p>
                <p class="text-2xl font-bold mb-6">29€ / Monat</p>
                <ul class="text-left text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ 5 Fahrer</li>
                    <li>✔ Gehaltsabrechnung</li>
                    <li>✔ E-Mail Support</li>
                </ul>
                <a href="#contact" class="block bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Auswählen</a>
            </div>

            <!-- Business -->
            <div class="bg-white p-6 rounded-lg shadow border-2 border-blue-600">
                <h4 class="text-xl font-semibold mb-2">Business</h4>
                <p class="text-gray-600 mb-4">Für wachsende Unternehmen</p>
                <p class="text-2xl font-bold mb-6">59€ / Monat</p>
                <ul class="text-left text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ 20 Fahrer</li>
                    <li>✔ Priorisierter Support</li>
                    <li>✔ Monatsberichte</li>
                </ul>
                <a href="#contact" class="block bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Auswählen</a>
            </div>

            <!-- Enterprise -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h4 class="text-xl font-semibold mb-2">Enterprise</h4>
                <p class="text-gray-600 mb-4">Für große Flotten</p>
                <p class="text-2xl font-bold mb-6">Individuell</p>
                <ul class="text-left text-sm text-gray-700 space-y-2 mb-6">
                    <li>✔ Unbegrenzte Fahrer</li>
                    <li>✔ Persönlicher Ansprechpartner</li>
                    <li>✔ Onboarding-Service</li>
                </ul>
                <a href="#contact" class="block bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Kontakt aufnehmen</a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-24 bg-white">
    <div class="max-w-xl mx-auto px-6 text-center">
        <h3 class="text-3xl font-bold mb-6">Kontaktieren Sie uns</h3>
        <p class="text-gray-600 mb-6">Fragen? Interesse an einer Demo? Schreiben Sie uns!</p>
        <form action="#" method="POST" class="space-y-4">
            <input type="text" name="name" placeholder="Ihr Name" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            <input type="email" name="email" placeholder="Ihre E-Mail-Adresse" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:outline-none">
            <textarea name="message" placeholder="Nachricht..." rows="4" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Absenden</button>
        </form>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-50 text-center py-6 text-sm text-gray-600">
    © {{ date('Y') }} TaxiPro – Alle Rechte vorbehalten.
</footer>

<script>
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });
</script>

</body>
</html>
