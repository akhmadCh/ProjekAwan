@extends('layout.app')

@section('title', 'Resources - MiniStack Cloud')

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
            <h2 class="text-3xl font-bold mb-2">Resource Management</h2>
            <p class="text-slate-600 dark:text-slate-400">Manage your compute and network resources.</p>
        </div>
        <button onclick="toggleModal('createResourceModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center gap-2 shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Resource
        </button>
    </div>

    <!-- Resource List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(empty($resources))
            <div class="col-span-full bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-12 text-center">
                <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">No Resources Yet</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Create a compute or network resource to get started.</p>
                <button onclick="toggleModal('createResourceModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200">
                    Create First Resource
                </button>
            </div>
        @else
            @foreach($resources as $resource)
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                        'running' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                        'stopped' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
                        'failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    ];
                    $typeLabels = [
                        'compute_metadata' => ['Compute', 'text-purple-600', 'bg-purple-100 dark:bg-purple-900/30'],
                        'network_metadata' => ['Network', 'text-cyan-600', 'bg-cyan-100 dark:bg-cyan-900/30'],
                    ];
                    $typeInfo = $typeLabels[$resource->type] ?? ['Resource', 'text-slate-600', 'bg-slate-100'];
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $typeInfo[2] }} {{ $typeInfo[1] }}">{{ $typeInfo[0] }}</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusColors[$resource->status] ?? '' }}">{{ ucfirst($resource->status) }}</span>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $resource->name }}</h4>
                        </div>
                    </div>

                    <div class="text-sm text-slate-500 dark:text-slate-400 space-y-1 mb-4">
                        <div>Created: {{ \Carbon\Carbon::parse($resource->created_at)->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('dashboard.resources.show', $resource->id) }}" class="flex-1 text-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold py-2 px-3 rounded-lg text-sm transition">
                            Details
                        </a>
                        @if($resource->status === 'stopped' || $resource->status === 'pending')
                            <form action="{{ route('dashboard.resources.start', $resource->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-3 rounded-lg text-sm transition">
                                    Start
                                </button>
                            </form>
                        @endif
                        @if($resource->status === 'running')
                            <form action="{{ route('dashboard.resources.stop', $resource->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-3 rounded-lg text-sm transition">
                                    Stop
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Create Resource Modal -->
<div id="createResourceModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="toggleModal('createResourceModal')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700 z-10">
            <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">
                            Create New Resource
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Create a new compute or network resource for your cloud infrastructure.
                            </p>
                        </div>

                        <form id="create-resource-form" action="{{ route('dashboard.resources.store') }}" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Resource Name</label>
                                <input type="text" name="name" placeholder="e.g., web-server-01" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Resource Type</label>
                                <select name="type" id="resource-type-select" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-white" required>
                                    <option value="compute_metadata">Compute (VM Instance)</option>
                                    <option value="network_metadata">Network (Virtual Network)</option>
                                </select>
                            </div>

                            <!-- Compute metadata fields -->
                            <div id="compute-fields">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">vCPU</label>
                                        <input type="number" name="metadata[vcpu]" value="1" min="1" max="16" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">RAM (MB)</label>
                                        <input type="number" name="metadata[ram_mb]" value="512" min="128" step="128" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-white">
                                    </div>
                                </div>
                            </div>

                            <!-- Network metadata fields -->
                            <div id="network-fields" class="hidden">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bandwidth (Mbps)</label>
                                    <input type="number" name="metadata[bandwidth_mbps]" value="100" min="1" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-white">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200 dark:border-slate-700">
                <button type="submit" form="create-resource-form" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                    Create Resource
                </button>
                <button type="button" onclick="toggleModal('createResourceModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    // Toggle metadata fields based on resource type
    const typeSelect = document.getElementById('resource-type-select');
    const computeFields = document.getElementById('compute-fields');
    const networkFields = document.getElementById('network-fields');

    typeSelect.addEventListener('change', function() {
        if (this.value === 'compute_metadata') {
            computeFields.classList.remove('hidden');
            networkFields.classList.add('hidden');
        } else {
            computeFields.classList.add('hidden');
            networkFields.classList.remove('hidden');
        }
    });
</script>
@endpush
