<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;

class CommonController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 17 Dec 2018
    */

    public function __construct()
    {
    	
    }


    //if want to redirect to any view we can use this function
    public function set_redirect_session(Request $request)
    {
        $redirect_to = $request->input('redirect_to');

        Session::put('redirect_to',$redirect_to);

        return response()->json(['status'=>'success']);
    }
}
