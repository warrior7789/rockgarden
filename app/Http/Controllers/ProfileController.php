<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function upload_file(Request $request)
    {
        $token= $request->bearerToken();

        $validator = Validator::make($request->all(),[ 
            'file' => 'required|mimes:jpg,jpeg,png,bmp,tiff |max:4096',
        ]);   
        if($validator->fails()) {          
           return response()->json(['error'=>$validator->errors()], 401);                        
        }

        if ($file = $request->file('file')) {
            $extension = $file->getClientOriginalExtension();
            $fullFilename = $file->getClientOriginalName();
            $filename = pathinfo($fullFilename, PATHINFO_FILENAME);

            $filenameToStore = $filename . "_" . time() . "." . $extension;
            $path = $file->move('storage/images', $filenameToStore);
            $fullPath = 'https://api-rockgarden.degreydigital.com/storage/images';

            return response()->json([
                "success" => true,
                "message" => $fullPath.'/'.$filenameToStore
            ]);
  
        }
    }

    public function update_photo(Request $request)
    {
        $token= $request->bearerToken();
        
        $validator = Validator::make($request->all(),[ 
            'url' => 'required|string',
        ]);   
        if($validator->fails()) {          
           return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $user = JWTAuth::authenticate($token);
        $user->file_img = $request->url;
        $user->save();
        
        if($user) {
            return response()->json(['success'=> true, 'message'=> "Photo updated successfully"]);
        }
        else {
            return response()->json(['error' => $validator->messages()], 500);
        }
    }
}