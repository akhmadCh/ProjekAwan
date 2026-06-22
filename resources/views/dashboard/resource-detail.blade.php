@extends('layout.app')

@section('title', 'Resource Detail - MiniStack Cloud')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg text-sm flex flex-col gap-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mb-10">
        <a href="{{ route('dashboard.resources') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to resources
        </a>
    </div>

    @php
        $statusColors = [
            'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
            'running' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
            'stopped' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
            'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
        ];
        $typeLabels = [
            'compute_metadata' => 'Compute Instance',
            'network_metadata' => 'Virtual Network',
            'storage_bucket' => 'Storage Bucket',
        ];
    @endphp

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-8 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">{{ $resource->name }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $typeLabels[$resource->type] ?? $resource->type }}</p>
            </div>
            <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $statusColors[$resource->status] ?? '' }}">{{ ucfirst($resource->status) }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Resource ID</p>
                    <p class="text-sm font-semibold font-mono">{{ $resource->id }}</p>
                </div>
                <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Type</p>
                    <p class="text-sm font-semibold">{{ $typeLabels[$resource->type] ?? $resource->type }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Created</p>
                    <p class="text-sm font-semibold">{{ \Carbon\Carbon::parse($resource->created_at)->format('M d, Y H:i:s') }}</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</p>
                    <p class="text-sm font-semibold">{{ ucfirst($resource->status) }}</p>
                </div>
                <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Subscription ID</p>
                    <p class="text-sm font-semibold font-mono">{{ $resource->user_subscription_id }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Last Updated</p>
                    <p class="text-sm font-semibold">{{ \Carbon\Carbon::parse($resource->updated_at)->format('M d, Y H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            @if($resource->status === 'stopped' || $resource->status === 'pending')
                <form action="{{ route('dashboard.resources.start', $resource->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Start Resource
                    </button>
                </form>
            @endif
            @if($resource->status === 'running')
                <form action="{{ route('dashboard.resources.stop', $resource->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                        Stop Resource
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Metadata Section -->
    @if(!empty($resource->metadata))
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-8">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Metadata</h3>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 overflow-x-auto">
                <pre class="text-sm text-slate-700 dark:text-slate-300 font-mono whitespace-pre-wrap">{{ json_encode($resource->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif
</div>
@endsection
