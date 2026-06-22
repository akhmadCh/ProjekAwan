<?php

namespace App\Services;

use App\Repositories\BucketRepository;
use App\Repositories\ObjectRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\SubscriptionPackageRepository;
use App\Repositories\UserSubscriptionRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Service untuk operasi storage:
 * - Hitung pemakaian storage
 * - Buat bucket (DB + S3)
 * - Upload object
 * - Validasi kuota
 */
class StorageService
{
    /**
     * Hitung total pemakaian storage user dalam GB.
     */
    public function getUsedStorageGb(int $userId): float
    {
        $bucketIds = BucketRepository::getIdsByUserId($userId);
        $totalMb = ObjectRepository::getTotalSizeMbByBucketIds($bucketIds);

        return round($totalMb / 1024, 4);
    }

    /**
     * Ambil overview storage user (used, total, remaining, percentage).
     */
    public function getStorageOverview(int $userId, object $package): array
    {
        $usedGb = $this->getUsedStorageGb($userId);
        $totalGb = (float) $package->storage_limit_gb;
        $remainingGb = max(0, $totalGb - $usedGb);
        $percentage = $totalGb > 0 ? min(100, round(($usedGb / $totalGb) * 100)) : 0;

        return [
            'used' => $usedGb,
            'total' => $totalGb,
            'remaining' => $remainingGb,
            'percentage' => $percentage,
        ];
    }

    /**
     * Buat bucket baru (record di DB + bucket fisik di S3).
     *
     * @return object bucket yang baru dibuat
     * @throws RuntimeException jika gagal
     */
    public function createBucket(int $userId, int $subscriptionId, string $bucketName): object
    {
        // 1. Buat resource record
        $resourceId = ResourceRepository::create([
            'user_id' => $userId,
            'user_subscription_id' => $subscriptionId,
            'name' => $bucketName,
            'type' => 'storage_bucket',
            'status' => 'running',
        ]);

        // 2. Buat bucket record
        $bucketId = BucketRepository::create([
            'resource_id' => $resourceId,
            'bucket_name' => $bucketName,
        ]);

        // 3. Buat bucket fisik di S3/MiniStack
        try {
            $s3Client = Storage::disk('s3')->getClient();
            $s3Client->createBucket(['Bucket' => $bucketName]);
        } catch (\Exception $e) {
            // Rollback: hapus record yang sudah dibuat
            ResourceRepository::softDelete($resourceId);
            throw new RuntimeException('Gagal membuat bucket di MiniStack: ' . $e->getMessage());
        }

        return BucketRepository::findById($bucketId);
    }

    /**
     * Upload object ke bucket.
     *
     * @return int ID object yang baru dibuat
     * @throws RuntimeException jika kuota habis
     */
    public function uploadObject(int $userId, int $bucketId, UploadedFile $file): int
    {
        $bucket = BucketRepository::findById($bucketId);
        if (! $bucket) {
            throw new RuntimeException('Bucket tidak ditemukan.');
        }

        // Validasi bahwa bucket ini milik user
        $resource = ResourceRepository::findById($bucket->resource_id);
        if (! $resource || (int) $resource->user_id !== $userId) {
            throw new RuntimeException('Anda tidak memiliki akses ke bucket ini.');
        }

        $sizeMb = round($file->getSize() / (1024 * 1024), 4);

        // Validasi kuota
        if (! $this->validateQuota($userId, $sizeMb)) {
            throw new RuntimeException('Kuota storage tidak mencukupi.');
        }

        // Upload ke S3
        $targetPath = 'user-' . $userId . '/' . $file->getClientOriginalName();
        config(['filesystems.disks.s3.bucket' => $bucket->bucket_name]);
        Storage::disk('s3')->put($targetPath, file_get_contents($file));

        // Simpan record ke DB
        $objectId = ObjectRepository::create([
            'bucket_id' => $bucketId,
            'object_key' => $targetPath,
            'original_filename' => $file->getClientOriginalName(),
            'size_mb' => $sizeMb,
            'mime_type' => $file->getClientMimeType(),
            'storage_path' => $targetPath,
        ]);

        // Update stats bucket
        BucketRepository::updateStorageStats($bucketId);

        return $objectId;
    }

    /**
     * Cek apakah user masih punya kuota untuk file tambahan.
     */
    public function validateQuota(int $userId, float $additionalMb): bool
    {
        $activeSub = UserSubscriptionRepository::findActiveByUserId($userId);
        if (! $activeSub) {
            return false;
        }

        $package = SubscriptionPackageRepository::findById($activeSub->package_id);
        if (! $package) {
            return false;
        }

        $usedGb = $this->getUsedStorageGb($userId);
        $additionalGb = $additionalMb / 1024;

        return ($usedGb + $additionalGb) <= $package->storage_limit_gb;
    }

    /**
     * Ambil semua bucket milik user beserta object-nya.
     */
    public function getBucketsWithObjects(int $userId): array
    {
        $buckets = BucketRepository::getByUserId($userId);

        foreach ($buckets as $bucket) {
            $bucket->objects = ObjectRepository::findByBucketId($bucket->id);
        }

        return $buckets;
    }
}
