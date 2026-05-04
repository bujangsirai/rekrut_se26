<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('AdminDashboardPage', [
            'user' => [
                'id' => $request->user()?->id,
                'username' => $request->user()?->username,
            ],
        ]);
    }
}
