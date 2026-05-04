<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LoginPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('LoginPage');
    }
}
