<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Models\User;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success([
            'users' => [
                'total'     => User::count(),
                'customers' => User::where('user_type', 'customer')->count(),
                'providers' => User::where('user_type', 'provider')->count(),
                'admins'    => User::where('user_type', 'admin')->count(),
                'active'    => User::where('status', 'active')->count(),
                'blocked'   => User::where('status', 'blocked')->count(),
            ],
            'providers' => [
                'total'    => Provider::count(),
                'verified' => Provider::where('is_verified', true)->count(),
                'pending'  => Provider::where('is_verified', false)->count(),
                'online'   => Provider::where('availability_status', 'online')->count(),
            ],
            'service_requests' => [
                'total'       => ServiceRequest::count(),
                'pending'     => ServiceRequest::where('status', 'pending')->count(),
                'in_progress' => ServiceRequest::where('status', 'in_progress')->count(),
                'completed'   => ServiceRequest::where('status', 'completed')->count(),
                'cancelled'   => ServiceRequest::where('status', 'cancelled')->count(),
            ],
            'payments' => [
                'total_paid'   => Payment::where('status', 'paid')->sum('amount'),
                'total_count'  => Payment::count(),
                'paid_count'   => Payment::where('status', 'paid')->count(),
                'pending'      => Payment::where('status', 'pending')->count(),
            ],
        ]);
    }
}
