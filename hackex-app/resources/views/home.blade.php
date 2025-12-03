@extends('layouts.app')

@section('title', 'HACKEX - Pre-Launch Security Scanner')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-br from-hackex-black via-gray-900 to-hackex-black text-white py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                Don't Launch <span class="text-sky-blue">Blind</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 mb-8">
                Detect real security risks before launching with AI-powered explanations anyone can understand.
            </p>
            <p class="text-lg text-gray-400 mb-12">
                Scan your website or source code in seconds. Get clear, actionable security guidance.
            </p>

            <!-- Scan Form -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-left" x-data="{ scanType: 'url' }">
                <form action="{{ route('scan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Scan Type Selector -->
                    <div class="flex gap-4 mb-6">
                        <button type="button" @click="scanType = 'url'" 
                                :class="scanType === 'url' ? 'bg-sky-blue text-white' : 'bg-gray-100 text-gray-700'"
                                class="flex-1 py-3 px-6 rounded-lg font-semibold transition">
                            🌐 Scan URL
                        </button>
                        <button type="button" @click="scanType = 'zip'" 
                                :class="scanType === 'zip' ? 'bg-sky-blue text-white' : 'bg-gray-100 text-gray-700'"
                                class="flex-1 py-3 px-6 rounded-lg font-semibold transition">
                            📦 Upload ZIP
                        </button>
                    </div>

                    <!-- URL Input -->
                    <div x-show="scanType === 'url'" class="mb-6">
                        <label for="url" class="block text-gray-700 font-semibold mb-2">Website URL</label>
                        <input type="url" name="url" id="url" 
                               placeholder="https://example.com"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-sky-blue focus:border-transparent"
                               value="{{ old('url') }}">
                        @error('url')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ZIP Upload -->
                    <div x-show="scanType === 'zip'" class="mb-6">
                        <label for="zip_file" class="block text-gray-700 font-semibold mb-2">Project ZIP File</label>
                        <input type="file" name="zip_file" id="zip_file" accept=".zip"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-sky-blue focus:border-transparent">
                        <p class="text-gray-500 text-sm mt-2">Max file size: 50MB</p>
                        @error('zip_file')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Consent Checkbox -->
                    <div class="mb-6">
                        <label class="flex items-start space-x-3">
                            <input type="checkbox" name="consent" value="1" required
                                   class="mt-1 w-5 h-5 text-sky-blue border-gray-300 rounded focus:ring-sky-blue">
                            <span class="text-gray-700 text-sm">
                                I confirm that I own this website or have explicit permission to scan it. 
                                Unauthorized scanning may be illegal.
                            </span>
                        </label>
                        @error('consent')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('rate_limit')
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-red-700">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-sky-blue hover:bg-blue-600 text-white py-4 px-6 rounded-lg font-bold text-lg transition shadow-lg hover:shadow-xl">
                        🔍 Start Free Security Scan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4">What HACKEX Detects</h2>
            <p class="text-xl text-gray-600">Comprehensive security scanning for modern web applications</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Runtime Scanning -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-4xl mb-4">🌐</div>
                <h3 class="text-2xl font-bold mb-4 text-sky-blue">Live Server Scan</h3>
                <ul class="space-y-2 text-gray-700">
                    <li>✓ SSL/HTTPS validation</li>
                    <li>✓ Security headers check</li>
                    <li>✓ Exposed admin panels</li>
                    <li>✓ Open dangerous ports</li>
                    <li>✓ Configuration leaks</li>
                    <li>✓ CORS misconfigurations</li>
                </ul>
            </div>

            <!-- Static Scanning -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-4xl mb-4">📦</div>
                <h3 class="text-2xl font-bold mb-4 text-sky-blue">Source Code Scan</h3>
                <ul class="space-y-2 text-gray-700">
                    <li>✓ Hardcoded API keys</li>
                    <li>✓ Exposed .env files</li>
                    <li>✓ Debug mode detection</li>
                    <li>✓ Private RSA keys</li>
                    <li>✓ Database dumps</li>
                    <li>✓ Sensitive logs</li>
                </ul>
            </div>

            <!-- AI Explanations -->
            <div class="bg-white rounded-xl shadow-lg p-8 hover:shadow-xl transition">
                <div class="text-4xl mb-4">🤖</div>
                <h3 class="text-2xl font-bold mb-4 text-sky-blue">AI Explanations</h3>
                <ul class="space-y-2 text-gray-700">
                    <li>✓ Plain English explanations</li>
                    <li>✓ Real-world attack scenarios</li>
                    <li>✓ Business impact analysis</li>
                    <li>✓ Clear fix recommendations</li>
                    <li>✓ No technical jargon</li>
                    <li>✓ Founder-friendly</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold mb-4">How It Works</h2>
            <p class="text-xl text-gray-600">Get security clarity in 3 simple steps</p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="space-y-8">
                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-sky-blue text-white rounded-full flex items-center justify-center text-xl font-bold">1</div>
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Submit Your URL or Code</h3>
                        <p class="text-gray-600">Paste your website URL or upload your project ZIP file. We support all major frameworks and platforms.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-sky-blue text-white rounded-full flex items-center justify-center text-xl font-bold">2</div>
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Automated Security Scan</h3>
                        <p class="text-gray-600">Our scanners check for 20+ common vulnerabilities including exposed secrets, weak configurations, and security misconfigurations.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-sky-blue text-white rounded-full flex items-center justify-center text-xl font-bold">3</div>
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Get Clear Recommendations</h3>
                        <p class="text-gray-600">Receive a security score, launch verdict, and AI-powered explanations with real-world attack scenarios and fix instructions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="bg-gradient-to-r from-sky-blue to-blue-600 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-4">Ready to Scan Your Project?</h2>
        <p class="text-xl mb-8">Get instant security feedback before you launch to the public.</p>
        <a href="#" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" 
           class="inline-block bg-white text-sky-blue hover:bg-gray-100 px-8 py-4 rounded-lg font-bold text-lg transition shadow-lg">
            Start Free Scan Now
        </a>
    </div>
</section>
@endsection
