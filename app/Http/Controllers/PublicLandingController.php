<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PublicLandingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin::PublicRecruitmentPage');
    }
}
