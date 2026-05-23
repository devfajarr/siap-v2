<?php

namespace App\Http\Controllers\V2\Dosen;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class BimbinganController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dosen/Bimbingan/Index');
    }
}
