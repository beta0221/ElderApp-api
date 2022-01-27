<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LineBotController extends Controller
{
    
    public function webhook(Request $request){
        return response('success',200);
    }

}
