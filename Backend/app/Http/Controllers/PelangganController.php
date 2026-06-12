<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function catalog()
    {
        return view('pelanggan.catalog');
    }

    public function checkout()
    {
        return view('pelanggan.checkout');
    }

    public function tracking()
    {
        return view('pelanggan.tracking');
    }
}
