<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Gmail;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ApiController extends BaseController
{   
    protected $maxAttempts = 3; // Default is 5
    protected $decayMinutes = 2; // Default is 1

    use AuthenticatesUsers;

    protected $response;

    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('first_name', 'last_name', 'middle_name', 'gender', 'phone_number', 'state_of_origin', 'home_address', 'state', 'city', 'email', 'password');
        $validator = Validator::make($data, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        $emailValidator = Validator::make($request->only('email'), [
            'email' => 'required|email|unique:users'
        ]);
        $passwordValidator = Validator::make($request->only('password'), [
            'password' => 'required|string|min:6|max:50'
        ]);
        if($passwordValidator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Password length must be upto six (6) characters.'
            ], 404);
        }
        if ($emailValidator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Email already in use.'
            ], 404);
        }
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Required field cannot be blank.'
            ], 404);
        }
    
        $otp = rand(1000,9999);
    
        $mail_details = [
            'title' => 'Testing Application OTP',
            'body' => 'Your OTP is : '. $otp
        ];
        Mail::to($request->email)->send(new Gmail($mail_details));
    
        //Request is valid, create new user
        $user = User::create([
        	'first_name' => $request->first_name,
        	'last_name' => $request->last_name,
        	'middle_name' => $request->middle_name,
        	'gender' => $request->gender,
        	'date_of_birth' => $request->date_of_birth,
        	'home_address' => $request->home_addres,
        	'office_addres' => $request->office_addres,
        	'city' => $request->city,
        	'state' => $request->state,
        	'phone_num' => $request->phone_number,
        	'email' => $request->email,
        	'password' => bcrypt($request->password),
        ]);
        User::where('email','=',$request->email)->update(['otp' => $otp]);
        $userId = User::where('email', '=', $request->email)->select('id')->first();
        DB::table('role_user')->insert(['role_id'=>3, 'user_id' => $userId['id']]);

        //User created, return success response
        return response()->json([
            'success' => true,
            'message' => $userId['id']
        ], Response::HTTP_OK);
    }
    public function verifyOtp(Request $request){
    
        $user  = User::where([['email', 'like', $request->email], ['otp','like',$request->otp]])->first();
        $otp  = User::where('otp','like',$request->otp)->first();
        $email = User::where('email', 'like', $request->email)->first();
        if(!$email) {
            return response(['success'=>false, 'message' => 'Account does not exist']);
        }
        if(!$otp) {
            return response(['success'=>false, 'message' => 'Invalid OTP']);
        }
        if($user){
            User::where('email','=',$request->email)->update(['otp' => 'null', 'is_verified' => 1]);
            if($user->is_verified == 1){
                return response()->json([
                    'success'=> true,
                    'message'=> 'Account already verified..'
                ]);
            }

            return response(['success'=> true, "message" => $user]);
        }
        else{

            return response(['success'=>false, 'message' => 'Account does not exist']);
        }
        
    }

    public function authenticate_(Request $request){
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid login credentials.'
            ], 404);
        }
        $credentials['is_verified'] = 1;

        $myTTL = 60*24*30; //minutes

        JWTAuth::factory()->setTTL($myTTL);
        $token = JWTAuth::attempt($credentials);
        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid login credentials.'
                ], 404);
            }
        } catch (JWTException $e) {
            return $credentials;
                return response()->json([
                        'success' => false,
                        'message' => 'Could not create token.',
                ], 500);
        }

        $user = User::where('email', $request->email)->first();
        $userId = User::where('email', $request->email)->select('id')->first();
        $roleId = DB::table('role_user')->where('user_id', $userId['id'])
                                        ->join('roles', 'roles.id', '=', 'role_user.role_id')
                                        ->select('roles.id')
                                        ->first();

 		//Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'message' => ['user'=> $user,'token'=> $token, 'user_role'=> $roleId]
            // 'token' => $token,
            // 'user_role' => $roleId
        ]);
    }

    public function authenticate(Request $request){
       $credentials = $request->only('email', 'password');

       //valid credential
       $validator = Validator::make($credentials, [
           'email' => 'required|email',
           'password' => 'required|string|min:6|max:50'
       ]);

       //Send failed response if request is not valid
       if ($validator->fails()) {
           return response()->json([
               'success' => false, 
               'message' => 'Invalid login credentials.'
           ], 404);
       }
       //$credentials['is_verified'] = 1;

       if (auth()->attempt($credentials)) {
           $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
           $user = User::find(auth()->user()->id);
           $roles = $user->getRoleNames();
           return response()->json([
               'success' => true,
               'message' => ['user'=> auth()->user(),'token'=> $token,'roles'=>$roles]
           ]);
       } else {
           $this->incrementLoginAttempts($request);
           return $this->sendError('Invalid login credentials.',[],0);
       }
               
       $token = auth()->user()->createToken('LaravelAuthApp')->accessToken; 

       $user = User::where('email', $request->email)->first();
       $userId = User::where('email', $request->email)->select('id')->first();

        //Token created, return with success response and jwt token
       
    }
 
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid token.'
            ], 404);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function get_user(Request $request)
    {
        $email = $_GET['email'];

        // $token= $request->bearerToken();
        // $user = JWTAuth::authenticate($token);
        $user = User::where('email', $email)->first();

        if(!$user) {
            return response()->json([
                'success' => false, 
                'message' => 'The email dose not exist']);
        } else {
            $roleId = DB::table('role_user')->where('user_id', $user['id'])
                        ->join('roles', 'roles.id', '=', 'role_user.role_id')
                        ->select('roles.id')
                        ->first();
        }
        return response()->json(['success' => true, 'message' => $user]);
    }
    public function profile_update(Request $request)
    {
        $data = $request->only('first_name', 'last_name ', 'middle_name', 'gender', 'date_of_birth', 'home_address', 'state_of_origin', 'office_address', 'city', 'state');
        $validator = Validator::make($data, [
            'first_name' => 'required|string',
            'middle_name' => 'required|string',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'home_address' => 'required',
            'office_address' => 'required',
            'city' => 'required',
            'state' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => $validator->errors()->first()
            ], 404);
        }

        $profile = User::where('id', auth()->user()->id)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'home_address' => $request->home_address,
            'office_address' => $request->office_address,
            'city' => $request->city,
            'state' => $request->state,
            'state_of_origin' => $request->state_of_origin
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
        ], Response::HTTP_OK);
    }

    protected function sendResetLinkResponse(Request $request)
    {
        $input = $request->only('email');
        $validator = Validator::make($input, [
            'email' => "required|email"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Required field cannot be blank.'
            ], 404);
        }
        $otp = rand(1000,9999);
        $response = User::where('email', $request->email)->first();

        if($response){
            $emailCheck = DB::table('password_resets')->where('email', '=', $request->email)->exists();

            if(!$emailCheck) {
                DB::table('password_resets')->insert([
                    'email' =>  $request->email,
                    'token' =>  $otp
                ]);
            } else {
                DB::table('password_resets')->where('email', $request->email)
                    ->update([
                        'email' =>  $request->email
                        // 'token' =>  $otp
                    ]);
            }        
            $mail_details = [
                'title' => 'Testing Application OTP',
                'body' => 'Your OTP is : '. $otp
            ];
            $mailSend = Mail::to($request->email)->send(new Gmail($mail_details));
            // echo $mailSend;
            // if(!$mailSend) {
            //     return response(["success" => false, "message" => "An error occurred please try again."]);
            // }
            DB::table('password_resets')->where('email', '=', $request->email)->update(['otp' => $otp]);

            $success = true;
            $message = "Mail send successfully";
        }else{
            $success = false;
            $message = "Account does not exist.";
        }

        //$message = $response == Password::RESET_LINK_SENT ? 'Mail send successfully' : GLOBAL_SOMETHING_WANTS_TO_WRONG;
        $response = ['success'=> $success, 'message' => $message];
        return response($response, 200);
    }
    protected function sendResetLinkResponseCheck(Request $request)
    {
        $forgotPassword = DB::table('password_resets')->where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
        
        if($forgotPassword){
            DB::table('password_resets')->where('email', '=', $request->email)->update(['is_verified' => 1]);
            return response(["success" => true, "message" => "Success"]);
        }
        else{
            return response(["success"=>false, 'message' => 'Invalid password']);
        }
    }
    protected function sendResetResponse(Request $request){
        //password.reset
        $input = $request->only('email','otp', 'new_password');
        $validator = Validator::make($input, [
            'otp' => 'required',
            'email' => 'required|email',
            'new_password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Required field cannot be blank.'
            ], 404);
        }

        // if(!$otpValidator->fails()) {
        //     return response()->json([
        //         'success' => false, 
        //         'message' => 'Invalid OTP.'
        //     ], 404);
        // }
        $emailValidator = Validator::make($request->only('email'), [
            'email' => 'required|email|unique:users'
        ]);
        if(!$emailValidator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Account does not exist.'
            ], 404);
        }
        $passwordValidator = Validator::make($request->only('new_password'), [
            'new_password' => 'required||string|min:6|max:50'
        ]);
        if($passwordValidator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Password length must be upto six (6) characters.'
            ], 404);
        }

        $otpValidator = User::where('otp', $request->otp)->first();
        if($otpValidator) {
            $response = User::where('email', $request->email)
                            ->update(['otp' => $request->otp, 'password' => bcrypt($request->new_password)]);
    
            if($response){
                $message = "Password reset successfully";
            }else{
                $message = "Email could not be sent to this email address";
            }
            
            $response = ['success'=> true, 'message' => $message];
            return response()->json($response);
        } else {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid Otp.'
            ], 404);
        }
    }
}