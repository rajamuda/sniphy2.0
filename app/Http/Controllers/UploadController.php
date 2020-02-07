<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function test (Request $request) {
    	sleep(20);
    	return header("HTTP/1.0 200 Ok");
    }
}
