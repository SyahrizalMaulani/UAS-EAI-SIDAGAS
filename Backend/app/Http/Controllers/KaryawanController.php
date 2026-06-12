<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function intake()
    {
        return view('karyawan.intake');
    }

    public function production()
    {
        return view('karyawan.production');
    }

    public function ready()
    {
        return view('karyawan.ready');
    }
}
