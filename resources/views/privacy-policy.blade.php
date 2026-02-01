<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Fleet Sync App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for legal lists */
        ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
        li { margin-bottom: 0.5rem; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans leading-relaxed">

<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="font-bold text-xl tracking-tight text-blue-900">
            FleetSync<span class="text-blue-500">.io</span>
        </div>
        <nav class="hidden md:flex space-x-6 text-sm font-medium text-gray-500">
            <a href="#" class="hover:text-blue-600 transition">Home</a>
            <a href="#" class="hover:text-blue-600 transition">Contact Support</a>
        </nav>
    </div>
</header>

<main class="max-w-4xl mx-auto px-6 py-12">

    <div class="mb-12 border-b border-gray-200 pb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4">Privacy Policy</h1>
        <p class="text-gray-500 text-sm">
            Last Updated: <span class="font-medium text-gray-700">February 1, 2026</span>
        </p>
    </div>

    <div class="space-y-10">

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
            <p class="mb-4">
                Welcome to <strong>Taxi Pro</strong> ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your fleet data. This Privacy Policy explains how we collect, use, disclosure, and safeguard your information when you use our application to sync data from the Uber Fleet API into your internal databases.
            </p>
            <p>
                By connecting your Uber Fleet account to our service, you agree to the collection and use of information in accordance with this policy.
            </p>
        </section>

        <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Information We Collect</h2>
            <p class="mb-4">
                To provide our fleet synchronization services, we authenticate with the Uber API and collect specific data points regarding your fleet operations. This includes:
            </p>
            <ul class="text-gray-700">
                <li><strong>Account Information:</strong> Your Uber account identifiers, email address, and profile information used for authentication (OAuth tokens).</li>
                <li><strong>Driver Data:</strong> Driver names, IDs, current status (online/offline), and performance metrics provided via the API.</li>
                <li><strong>Vehicle Data:</strong> Vehicle identification numbers (VIN), license plates, make/model, and real-time or historical location data (GPS coordinates).</li>
                <li><strong>Trip Data:</strong> Start and end times, trip routes, fares, and earnings data associated with your fleet.</li>
                <li><strong>Document Data:</strong> Status of insurance papers, registration, and driver licenses if synced for compliance monitoring.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">3. How We Use Your Information</h2>
            <p class="mb-4">We use the data collected strictly for the purpose of helping you manage your fleet operations. Specifically, we use it to:</p>
            <ol class="text-gray-700">
                <li><strong>Sync and Backup:</strong> Automatically transfer fleet activity data from Uber servers to your designated database for record-keeping.</li>
                <li><strong>Analytics:</strong> Generate performance reports, calculate fleet efficiency, and monitor driver earnings.</li>
                <li><strong>Compliance:</strong> Alert you when vehicle documents or driver licenses are about to expire based on Uber's records.</li>
                <li><strong>Operational Support:</strong> Debug synchronization errors and provide customer support.</li>
            </ol>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mt-4">
                <p class="text-sm text-blue-800">
                    <strong>Note on Uber Data:</strong> We do not use data obtained through the Uber API for advertising purposes, nor do we sell this data to data brokers.
                </p>
            </div>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Data Retention and Storage</h2>
            <p class="mb-4">
                We retain your personal and fleet data only for as long as is necessary for the purposes set out in this Privacy Policy.
            </p>
            <p>
                Data retrieved from the Uber API is stored securely in your database instance. If you disconnect your Uber account from our application, we will delete authentication tokens immediately. Historical fleet data stored in your database remains under your control and ownership.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Disclosure of Your Information</h2>
            <p class="mb-4">We may share information in the following situations:</p>
            <ul class="text-gray-700">
                <li><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to respond to legal process, to investigate or remedy potential violations of our policies, or to protect the rights, property, and safety of others.</li>
                <li><strong>Service Providers:</strong> We may share data with cloud hosting providers (e.g., AWS, Google Cloud) solely for the purpose of hosting the application infrastructure.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Security of Your Data</h2>
            <p class="mb-4">
                We use administrative, technical, and physical security measures to help protect your personal information. This includes encrypting API tokens at rest and using SSL/TLS for all data in transit between Uber, our servers, and your database. However, please be aware that no security measures are perfect or impenetrable.
            </p>
        </section>

        <section class="bg-gray-100 p-6 rounded-lg">
            <h2 class="text-xl font-bold text-gray-900 mb-2">7. Uber API Terms of Use</h2>
            <p class="text-sm text-gray-700 mb-0">
                Our use of information received from Uber APIs will adhere to the <a href="https://developer.uber.com/docs/riders/terms" target="_blank" class="text-blue-600 underline hover:text-blue-800">Uber API Terms of Use</a>. We clearly display the Uber logo to indicate the source of the data but are not affiliated with, endorsed, or sponsored by Uber Technologies Inc.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Contact Us</h2>
            <p class="mb-4">
                If you have questions or comments about this Privacy Policy, or if you wish to request the deletion of your data, please contact us at:
            </p>
            <div class="mt-4">
                <p class="font-medium text-gray-900">Sanli Technology Limited</p>
                <p class="text-gray-600">15 George Court Newport Road,</p>
                <p class="text-gray-600">Cardiff, Wales, CF24 1DP, United Kingdom</p>
                <p class="text-gray-600 mt-2">Email: <a href="mailto:sanli.web@hotmail.com" class="text-blue-600 hover:underline">sanli.web@hotmail.com</a></p>
            </div>
        </section>

    </div>
</main>

<footer class="bg-gray-900 text-white py-12 mt-12">
    <div class="max-w-4xl mx-auto px-6 grid md:grid-cols-2 gap-8">
        <div>
            <h3 class="font-bold text-lg mb-2">Taxi Pro</h3>
            <p class="text-gray-400 text-sm">Empowering fleet owners with real-time data synchronization.</p>
        </div>
        <div class="md:text-right text-gray-400 text-sm">
            <p>&copy; 2026 Sanli Technology Limited. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
