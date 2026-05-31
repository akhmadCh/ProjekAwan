@extends('layout.app')

@section('title', 'Storage - MiniStack Cloud')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

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
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to dashboard
        </a>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold mb-2">Storage Management</h2>
            <p class="text-slate-600 dark:text-slate-400">Monitor your active storage buckets and access credentials.</p>
        </div>
        <button onclick="toggleModal('credentialModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center gap-2 shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create New Bucket
        </button>
    </div>

    <div class="mb-6">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide flex items-center gap-2 mb-4">
            <span class="w-2 h-2 rounded-full bg-green-500"></span> Active Buckets
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($resources->isEmpty())
                <div class="col-span-full bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-12 text-center">
                    <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">No Active Buckets</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Create a bucket to start uploading and storing files.</p>
                    <button onclick="toggleModal('credentialModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                        Create First Bucket
                    </button>
                </div>
            @else
                @foreach($resources as $resource)
                    @php
                        $usedMb = $resource->objects->sum('size_mb');
                        $usedGB = round($usedMb / 1024, 4);
                        $quotaGB = $storageData['total'];
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-6">
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    {{ $resource->name }}
                                </h4>
                                <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs font-semibold px-2 py-0.5 rounded-full">{{ ucfirst($resource->status) }}</span>
                            </div>

                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                                    <span class="text-slate-500 dark:text-slate-400">Used Storage</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">
                                        {{ $usedMb >= 1024 ? number_format($usedGB, 2) . ' GB' : number_format($usedMb, 2) . ' MB' }} of {{ $quotaGB }} GB
                                    </span>
                                </div>
                                <div class="flex justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                                    <span class="text-slate-500 dark:text-slate-400">Objects Count</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $resource->objects->count() }} files</span>
                                </div>
                                <div class="flex justify-between items-center pt-1">
                                    <span class="text-slate-500 dark:text-slate-400">Access Key</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ auth()->user()->credentials->access_key ?? 'N/A' }}</span>
                                        @if(auth()->user()->credentials)
                                            <button onclick="copyToClipboard('{{ auth()->user()->credentials->access_key }}', 'btn-key-{{ $resource->id }}')" id="btn-key-{{ $resource->id }}-btn" class="text-slate-400 hover:text-blue-600 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- List of uploaded files in this bucket -->
                            @if($resource->objects->isNotEmpty())
                                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Files:</h5>
                                    <ul class="space-y-1 max-h-32 overflow-y-auto">
                                        @foreach($resource->objects as $object)
                                            <li class="flex justify-between items-center text-xs border-b border-slate-50 dark:border-slate-700/50 pb-1">
                                                <span class="text-slate-700 dark:text-slate-300 truncate max-w-[200px]" title="{{ basename($object->key) }}">
                                                    {{ basename($object->key) }}
                                                </span>
                                                <span class="text-slate-400 font-mono text-[10px]">
                                                    {{ $object->size_mb >= 1 ? number_format($object->size_mb, 2) . ' MB' : number_format($object->size_mb * 1024, 2) . ' KB' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Upload form for this bucket -->
                        <form action="{{ route('dashboard.storage.upload') }}" method="POST" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            @csrf
                            <input type="hidden" name="resource_id" value="{{ $resource->id }}">
                            <div class="flex items-center gap-2">
                                <input type="file" name="dokumen" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" required>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded-lg text-xs transition duration-200 flex-shrink-0">
                                    Upload
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
<div id="credentialModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('credentialModal')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700 z-10">
            <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">
                            Create New Storage Bucket
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Enter a unique name for your new storage bucket. S3 bucket names must be lowercase, alphanumeric, and may contain hyphens or dots.
                            </p>
                        </div>

                        <form id="create-bucket-form" action="{{ route('dashboard.storage.buckets.store') }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Bucket Name</label>
                                <input type="text" name="bucket_name" placeholder="e.g., my-app-assets" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Access Level</label>
                                <select class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 dark:text-white">
                                    <option>Read & Write (Full Access)</option>
                                    <option>Read Only</option>
                                </select>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200 dark:border-slate-700">
                <button type="submit" form="create-bucket-form" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                    Create Bucket
                </button>
                <button type="button" onclick="toggleModal('credentialModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // fungsi untuk menyalin kredensial
    function copyToClipboard(text, elementId) {
        navigator.clipboard.writeText(text).then(() => {
            const button = document.getElementById(elementId + '-btn');
            const originalHTML = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        });
    }

    // fungsi untuk membuka dan menutup modal
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
</script>
@endpush
