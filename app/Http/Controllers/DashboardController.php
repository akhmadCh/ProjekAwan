<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
