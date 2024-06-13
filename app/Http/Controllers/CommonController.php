<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\User;

class CommonController extends Controller
{
    public function checkEmail($email)
    {
       // print_r($email); die();
        $isID = User::select('id')->where('email','=',$email)->get()->toArray();
        if(count($isID) > 0) $id = $isID[0]['id'];
        else $id ='';
        return $id;
       // print_r($isID); die();
    }

    public function checkAnhCreateEmail($data)
    {
        // print_r($email); die();
        $email =$data['shipment_email'];
        $isID = User::select('id')->where('email','=',$email)->get()->toArray();

        $id ='';
        if(count($isID) > 0) $id = $isID[0]['id'];
        else {
           $user = User::create([
                'first_name' => $data['full_name'],
                //'last_name' => $data['last_name'],
                'email' => $data['shipment_email'],
                'address' => $data['shipment_address'],
                'password' => Hash::make($data['shop']),
            ]);

            $id = $user->id;
        }
        return $id;
    }
}