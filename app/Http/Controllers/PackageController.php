<?php

namespace App\Http\Controllers;

use App\Models\PremiumPackage;

class PackageController extends Controller
{
    public function index($packageId, $lan = 'bn')
    {
        $package = PremiumPackage::find($packageId);

        $data = [
           'package' => $package,
           'lan' => $lan,
        ];

        return view('package.index')->with($data);
    }
}
