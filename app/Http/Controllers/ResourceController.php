<?php

namespace App\Http\Controllers;

use App\Services\ResourceService;
use App\Services\UserSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Controller untuk mengelola compute & network resources.
 * Thin controller — semua logic di ResourceService.
 */
class ResourceController extends Controller
{
    public function __construct(
        private ResourceService $resourceService,
        private UserSetupService $userSetupService
    ) {}

    /**
     * Halaman daftar resource (compute & network).
     */
    public function index()
    {
        $user = Auth::user();
        $this->userSetupService->ensureUserIsReady($user->id);

        $resources = $this->resourceService->getResourcesByUser($user->id);

        return view('dashboard.resources', compact('resources'));
    }

    /**
     * Buat resource baru (compute_metadata atau network_metadata).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'type' => 'required|in:compute_metadata,network_metadata',
            'metadata' => 'nullable|array',
            'metadata.vcpu' => 'nullable|integer|min:1',
            'metadata.ram_mb' => 'nullable|integer|min:128',
            'metadata.bandwidth_mbps' => 'nullable|integer|min:1',
        ]);

        try {
            $resourceId = $this->resourceService->createResource(Auth::id(), [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'metadata' => $validated['metadata'] ?? null,
            ]);

            return redirect()->route('dashboard.resources')
                ->with('success', "Resource '{$validated['name']}' berhasil dibuat!");
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->withErrors(['name' => $e->getMessage()]);
        }
    }

    /**
     * Tampilkan detail resource.
     */
    public function show(int $id)
    {
        $resource = $this->resourceService->getResourceDetail(Auth::id(), $id);

        if (! $resource) {
            abort(404, 'Resource tidak ditemukan.');
        }

        return view('dashboard.resource-detail', compact('resource'));
    }

    /**
     * Start resource.
     */
    public function start(int $id)
    {
        try {
            $this->resourceService->startResource(Auth::id(), $id);
            return redirect()->back()->with('success', 'Resource berhasil dijalankan!');
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['resource' => $e->getMessage()]);
        }
    }

    /**
     * Stop resource.
     */
    public function stop(int $id)
    {
        try {
            $this->resourceService->stopResource(Auth::id(), $id);
            return redirect()->back()->with('success', 'Resource berhasil dihentikan!');
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['resource' => $e->getMessage()]);
        }
    }
}
