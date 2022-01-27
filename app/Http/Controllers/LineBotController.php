<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LineBotController extends Controller
{
    
    public function webhook(Request $request){



        $channelSecret = '845775fbea34b0c480bdc4b84c47d078'; // Channel secret string
        $httpRequestBody = $request->getContent(); // Request body string
        $hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true);
        $signature = base64_encode($hash);

        $x_line_signature = $request->header('x-line-signature');

        if($signature != $x_line_signature){
            return response('error',500);
        }





        return response('success',200);
    }

}
