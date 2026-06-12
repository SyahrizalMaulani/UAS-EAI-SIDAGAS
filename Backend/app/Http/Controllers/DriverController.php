<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function deliveries()
    {
        return view('driver.deliveries');
    }

    public function active()
    {
        return view('driver.active');
    }

    public function history()
    {
        return view('driver.history');
    }
}
