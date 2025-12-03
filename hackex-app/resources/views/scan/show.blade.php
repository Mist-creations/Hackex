@extends('layouts.app')

@section('title', 'Scan Results - HACKEX')

@section('content')
<div class="container mx-auto px-4 py-12">
    @if($scan->isScanning())
        <!-- Scanning in Progress -->
        <div class="max-w-2xl mx-auto" x-data="scanStatus('{{ $scanId }}')" x-init="startPolling()">
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="mb-8">
                    <svg class="animate-spin h-20 w-20 text-sky-blue mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <h2 class="text-3xl font-bold mb-4">Scanning Your Project...</h2>
                <p class="text-gray-600 mb-8" x-text="statusMessage">Initializing security scan...</p>

                <div class="bg-gray-100 rounded-lg p-4">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>Scan ID: #{{ $scan->id }}</span>
                        <span x-text="'Status: ' + status">Status: {{ $scan->status }}</span>
                    </div>
                </div>
            </div>
        </div>

    @elseif($scan->status === 'failed')
        <!-- Scan Failed -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-12 text-center">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-3xl font-bold text-red-700 mb-4">Scan Failed</h2>
                <p class="text-gray-700 mb-8">We encountered an error while scanning your project. Please try again.</p>
                <a href="{{ route('home') }}" class="inline-block bg-sky-blue hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold transition">
                    Start New Scan
                </a>
            </div>
        </div>

    @else
        <!-- Scan Complete - Show Results -->
        <div class="max-w-6xl mx-auto">
            <!-- Score Card -->
            <div class="bg-gradient-to-br from-hackex-black to-gray-900 text-white rounded-2xl shadow-2xl p-12 mb-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold mb-8">Security Scan Results</h1>
                    
                    <div class="flex items-center justify-center space-x-12 mb-8">
                        <!-- Score -->
                        <div>
                            <div class="text-7xl font-bold mb-2 
                                @if($scan->score >= 80) text-green-400
                                @elseif($scan->score >= 50) text-yellow-400
                                @else text-red-400
                                @endif">
                                {{ $scan->score }}
                            </div>
                            <div class="text-gray-400 text-lg">Security Score</div>
                        </div>

                        <!-- Verdict -->
                        <div class="text-left">
                            <div class="inline-block px-6 py-3 rounded-lg font-bold text-xl
                                @if($scan->verdict === 'Safe for Launch') bg-green-500
                                @elseif($scan->verdict === 'Risky – Fix Recommended') bg-yellow-500 text-gray-900
                                @else bg-red-500
                                @endif">
                                @if($scan->verdict === 'Safe for Launch') ✅
                                @elseif($scan->verdict === 'Risky – Fix Recommended') ⚠️
                                @else ❌
                                @endif
                                {{ $scan->verdict }}
                            </div>
                            <div class="text-gray-400 mt-2">
                                Found {{ $scan->findings->count() }} security {{ Str::plural('issue', $scan->findings->count()) }}
                            </div>
                        </div>
                    </div>

                    @if($scan->input_url)
                        <div class="text-gray-400">
                            Scanned: <span class="text-sky-blue">{{ $scan->input_url }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($scan->findings->count() > 0)
                <!-- Findings by Severity -->
                @foreach(['critical', 'high', 'medium', 'low'] as $severity)
                    @if($findingsBySeverity[$severity]->count() > 0)
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold mb-4 flex items-center">
                                <span class="inline-block w-3 h-3 rounded-full mr-3
                                    @if($severity === 'critical') bg-red-500
                                    @elseif($severity === 'high') bg-orange-500
                                    @elseif($severity === 'medium') bg-yellow-500
                                    @else bg-blue-500
                                    @endif"></span>
                                {{ ucfirst($severity) }} Severity Issues ({{ $findingsBySeverity[$severity]->count() }})
                            </h2>

                            <div class="space-y-4">
                                @foreach($findingsBySeverity[$severity] as $finding)
                                    <div class="bg-white rounded-xl shadow-lg overflow-hidden" x-data="{ expanded: false }">
                                        <!-- Finding Header -->
                                        <div class="p-6 cursor-pointer hover:bg-gray-50 transition" @click="expanded = !expanded">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3 mb-2">
                                                        <span class="text-2xl">{{ $finding->severity_icon }}</span>
                                                        <h3 class="text-xl font-bold">{{ $finding->title }}</h3>
                                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                                            {{ $finding->type_badge }}
                                                        </span>
                                                    </div>
                                                    
                                                    @if($finding->location)
                                                        <p class="text-gray-600 text-sm mb-2">
                                                            📍 <code class="bg-gray-100 px-2 py-1 rounded">{{ $finding->location }}</code>
                                                        </p>
                                                    @endif

                                                    @if($finding->evidence)
                                                        <p class="text-gray-700">{{ $finding->evidence }}</p>
                                                    @endif
                                                </div>

                                                <button class="text-sky-blue hover:text-blue-600 transition">
                                                    <svg class="w-6 h-6 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- AI Explanation (Expandable) -->
                                        <div x-show="expanded" x-collapse class="border-t border-gray-200 bg-gray-50 p-6">
                                            @if($finding->ai_explanation)
                                                <div class="mb-6">
                                                    <h4 class="font-bold text-lg mb-2 text-sky-blue">💡 What This Means</h4>
                                                    <p class="text-gray-700">{{ $finding->ai_explanation }}</p>
                                                </div>
                                            @endif

                                            @if($finding->ai_attack_scenario)
                                                <div class="mb-6">
                                                    <h4 class="font-bold text-lg mb-2 text-red-600">⚠️ Real-World Attack Scenario</h4>
                                                    <p class="text-gray-700">{{ $finding->ai_attack_scenario }}</p>
                                                </div>
                                            @endif

                                            @if($finding->fix_recommendation)
                                                <div>
                                                    <h4 class="font-bold text-lg mb-2 text-green-600">✅ How To Fix</h4>
                                                    <p class="text-gray-700">{{ $finding->fix_recommendation }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <!-- No Issues Found -->
                <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-12 text-center">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="text-3xl font-bold text-green-700 mb-4">No Security Issues Found!</h2>
                    <p class="text-gray-700 text-lg">Your project passed all security checks. Great job!</p>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-12 text-center">
                <a href="{{ route('home') }}" class="inline-block bg-sky-blue hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold transition">
                    Scan Another Project
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function scanStatus(scanId) {
    return {
        status: '{{ $scan->status }}',
        statusMessage: 'Initializing security scan...',
        polling: null,

        startPolling() {
            const messages = [
                'Checking SSL certificate...',
                'Analyzing security headers...',
                'Scanning for exposed secrets...',
                'Checking admin panel exposure...',
                'Testing port configurations...',
                'Analyzing source code...',
                'Generating AI explanations...',
            ];

            let messageIndex = 0;
            setInterval(() => {
                this.statusMessage = messages[messageIndex % messages.length];
                messageIndex++;
            }, 3000);

            this.polling = setInterval(() => {
                fetch(`/scan/${scanId}/status`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Scan not found');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.status = data.status;
                        if (data.is_complete) {
                            clearInterval(this.polling);
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Polling error:', error);
                        // Continue polling - scan might still be processing
                    });
            }, 3000);
        }
    }
}
</script>
@endpush
