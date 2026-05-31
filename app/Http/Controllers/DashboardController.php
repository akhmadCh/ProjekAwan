<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToCheckFileExistence;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard data
        $userData = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'username' => auth()->user()->username,
            'joinedDate' => auth()->user()->created_at->format('M d, Y'),
            'status' => 'Active',
        ];

        $subscriptionData = [
            'plan' => 'Basic',
            'status' => 'Active',
            'storage' => '5 GB',
            'renewalDate' => now()->addMonths(1)->format('M d, Y'),
            'price' => '$9.99/month',
        ];

        $storageData = [
            'used' => 1.2,
            'total' => 5,
            'remaining' => 3.8,
            'percentage' => 24,
        ];

        $credentialsData = [
            'accessKey' => 'MINI1234567890ABCD',
            'secretKey' => 'abcdef1234567890secretkey',
        ];

        return view('dashboard.index', compact('userData', 'subscriptionData', 'storageData', 'credentialsData'));
    }

    public function storage()
    {
        $disk = Storage::disk('s3');
        
        // Definisikan kuota statis atau ambil dari database sesuai paket langganan
        $totalQuotaGB = 5; 

        try {
            // Ambil semua file yang ada di dalam bucket MiniStack Anda
            $files = $disk->allFiles();
            
            $totalUsedBytes = 0;
            foreach ($files as $file) {
                // Tambahkan try-catch kecil per file jika ada masalah hak akses pada file tertentu
                try {
                    $totalUsedBytes += $disk->size($file);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Konversi ukuran bytes ke Gigabytes (GB)
            $usedGB = round($totalUsedBytes / (1024 * 1024 * 1024), 4);
            $remainingGB = $totalQuotaGB - $usedGB;
            $percentage = $totalQuotaGB > 0 ? round(($usedGB / $totalQuotaGB) * 100) : 0;

            $storageData = [
                'used' => $usedGB,
                'total' => $totalQuotaGB,
                'remaining' => $remainingGB > 0 ? $remainingGB : 0,
                'percentage' => $percentage > 100 ? 100 : $percentage,
                'file_count' => count($files)
            ];

        } catch (\Exception $e) {
            // Jika ada masalah koneksi mendadak ke MiniStack, siapkan fallback data agar aplikasi tidak crash
            $storageData = [
                'used' => 0.0,
                'total' => $totalQuotaGB,
                'remaining' => $totalQuotaGB,
                'percentage' => 0,
                'file_count' => 0,
                'error_message' => 'Gagal berkomunikasi dengan server cloud emulasi.'
            ];
        }

        return view('dashboard.storage', compact('storageData'));
    }

    public function subscription()
    {
        // data dummy khusus halaman langganan
        return view('dashboard.subscription');
    }

    // test koneksi ke MiniStack S3 emulator
    public function uploadObject(Request $request)
    {
        $file = $request->file('dokumen');
        
        // Ini akan otomatis membuat file di dalam container MiniStack Anda!
        Storage::disk('s3')->put('objects/' . $file->getClientOriginalName(), file_get_contents($file));

        // Simpan metadatanya ke database lokal Anda seperti migrasi yang telah Anda buat
        ObjectFile::create([
            'user_id' => Auth::id(),
            'bucket_name' => 'projekawan',
            'object_key' => 'objects/' . $file->getClientOriginalName(),
            'content_type' => $file->getClientMimeType(),
            'content_length' => $file->getSize(),
        ]);
    }
}
