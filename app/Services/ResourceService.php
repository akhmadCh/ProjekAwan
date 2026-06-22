<?php

namespace App\Services;

use App\Repositories\LogRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\UserSubscriptionRepository;
use RuntimeException;

/**
 * Service untuk mengelola compute & network resources:
 * - Buat resource (compute_metadata, network_metadata)
 * - Start / Stop resource
 * - Lihat detail resource
 */
class ResourceService
{
    /**
     * Buat resource baru (compute atau network).
     *
     * @return int ID resource yang baru dibuat
     * @throws RuntimeException jika subscription tidak aktif
     */
    public function createResource(int $userId, array $data): int
    {
        $activeSub = UserSubscriptionRepository::findActiveByUserId($userId);
        if (! $activeSub) {
            throw new RuntimeException('Tidak ada subscription aktif.');
        }

        $resourceId = ResourceRepository::create([
            'user_id' => $userId,
            'user_subscription_id' => $activeSub->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'status' => 'pending',
            'metadata' => $data['metadata'] ?? null,
        ]);

        // Log aksi pembuatan resource
        LogRepository::create([
            'user_id' => $userId,
            'action' => 'create_resource',
            'target_type' => 'resource',
            'target_id' => $resourceId,
            'description' => "Created {$data['type']} resource: {$data['name']}",
        ]);

        return $resourceId;
    }

    /**
     * Start resource (ubah status ke 'running').
     */
    public function startResource(int $userId, int $resourceId): bool
    {
        $resource = ResourceRepository::findByUserIdAndId($userId, $resourceId);
        if (! $resource) {
            throw new RuntimeException('Resource tidak ditemukan.');
        }

        if ($resource->status === 'running') {
            throw new RuntimeException('Resource sudah berjalan.');
        }

        $result = ResourceRepository::updateStatus($resourceId, 'running');

        LogRepository::create([
            'user_id' => $userId,
            'action' => 'start_resource',
            'target_type' => 'resource',
            'target_id' => $resourceId,
            'description' => "Started resource: {$resource->name}",
        ]);

        return $result;
    }

    /**
     * Stop resource (ubah status ke 'stopped').
     */
    public function stopResource(int $userId, int $resourceId): bool
    {
        $resource = ResourceRepository::findByUserIdAndId($userId, $resourceId);
        if (! $resource) {
            throw new RuntimeException('Resource tidak ditemukan.');
        }

        if ($resource->status === 'stopped') {
            throw new RuntimeException('Resource sudah dihentikan.');
        }

        $result = ResourceRepository::updateStatus($resourceId, 'stopped');

        LogRepository::create([
            'user_id' => $userId,
            'action' => 'stop_resource',
            'target_type' => 'resource',
            'target_id' => $resourceId,
            'description' => "Stopped resource: {$resource->name}",
        ]);

        return $result;
    }

    /**
     * Ambil semua resource milik user (non-storage).
     * Storage bucket dikelola terpisah via StorageService.
     */
    public function getResourcesByUser(int $userId): array
    {
        $allResources = ResourceRepository::findByUserId($userId);

        // Filter hanya compute & network resources
        return array_values(array_filter($allResources, function ($r) {
            return in_array($r->type, ['compute_metadata', 'network_metadata']);
        }));
    }

    /**
     * Ambil semua resource milik user (semua type).
     */
    public function getAllResourcesByUser(int $userId): array
    {
        return ResourceRepository::findByUserId($userId);
    }

    /**
     * Ambil detail resource tertentu milik user.
     */
    public function getResourceDetail(int $userId, int $resourceId): ?object
    {
        $resource = ResourceRepository::findByUserIdAndId($userId, $resourceId);
        if (! $resource) {
            return null;
        }

        // Decode metadata JSON
        if (isset($resource->metadata) && is_string($resource->metadata)) {
            $resource->metadata = json_decode($resource->metadata, true);
        }

        return $resource;
    }
}
