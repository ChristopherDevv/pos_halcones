<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Interfaces\DataResponse;
use Illuminate\Support\Facades\Crypt;


class ConfigController extends Controller
{
    public function show(){
        try{
            $data = request(['name']);
            $config = Config::where('name',$data)->select('value')->first();
            return $config;
        }catch(\Exception $e){
            $response = new DataResponse($e->getMessage(),'Error',$e->getTrace());
            return response()->json($response,500);
        }
    }

    public function showIdPaypal(){
        try {
            $config = Config::where('name','idApiPaypal')->select('value')->firstOrFail();
            $encrypt = base64_encode($config->value);
            return [
                'value' => $encrypt
            ];
        } catch (\Throwable $e) {
            $response = new DataResponse($e->getMessage(),'Error',$e->getTrace());
            return response()->json($response,500);
        }
    }

    public function showMembresia(){
        try{
            $data = request(['name']);
            $config = Config::where('name',$data)->select('value')->first();
            return $config;
        }catch(\Exception $e){
            $response = new DataResponse($e->getMessage(),'Error',$e->getTrace());
            return response()->json($response,500);
        }
    }
}
