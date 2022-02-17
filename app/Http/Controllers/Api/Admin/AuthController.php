<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

use Validator;
//use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as VPassword;
use Illuminate\Support\Facades\Config;
use DB;

class AuthController extends BaseController
{
	/**
	* Registration
	*/
	protected $response;
	//http://localhost:8001/api/admin/registration
	public function register(Request $request){	

		dd("dsafdsaf");
		
		$validator = Validator::make($request->all(), [
			'name' 			=> 'required|min:1',
			'email' 		=> 'required|email|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users,email',
			'phone'     	=> 'required|unique:users',
			'birthdate' 	=> 'required|date',
			'province_id' 	=> 'required|integer',
			'town_id' 		=> 'required|integer',
			'password' 		=> 'required',			
			'avatar'    	=> 'required|image|mimes:jpeg,png,jpg,gif,svg',
			'refcode'		=> 'required|exists:users,refcode',
			/*'password' => [
		        'required',
		        'string',
		        VPassword::min(8)->mixedCase()->numbers()->symbols()->uncompromised()
		    ],*/

		]);
	    if ($validator->fails()) {
	       return $this->sendError($validator->errors()->first(), [],0);
	    }
	    $user_referrals = array();
	    $parent = User::where('refcode',$request->refcode)->first();

	    if(empty($parent)){
	    	return $this->sendError(__('api.referral_not_found'),[],0);
	    }

	    $birthdate = Carbon::createFromFormat('d-m-Y', $request->birthdate)->format('Y-m-d');
	    $province = Province::find($request->province_id);
	    $province_name = str_replace(' ','',$province->name); 

	    $SiteSetting = \App\models\SiteSetting::where('key','RUNNIG_WEEK')->first();

	    if(!empty($SiteSetting->value)){
		    $week = $SiteSetting->value;
		    if($SiteSetting->value=='week1'){
		    	$SiteSetting->value ='week2';
		    }elseif($SiteSetting->value=='week2'){
		    	$SiteSetting->value ='week3';
		    }elseif($SiteSetting->value=='week3'){
		    	$SiteSetting->value ='week4';
		    }elseif($SiteSetting->value=='week4'){
		    	$SiteSetting->value ='week1';
		    }
		}
	    if(empty($SiteSetting->value)){
	    	$SiteSetting_ = new SiteSetting();
	    	$week = "week1";
	    	$SiteSetting_->value = "week1";
	    	$SiteSetting_->key = "RUNNIG_WEEK";
	    	$SiteSetting_->save();
	    }else{
	    	$SiteSetting->save();

	    }

	    $user = User::create([
	        'week_withdraw' => $week,
	        'name' 			=> $request->name,
	        'email' 		=> $request->email,
	        'birthdate' 	=> $birthdate,
	        'province_id' 	=> $request->province_id,
	        'town_id' 		=> $request->town_id,
	        'phone' 		=> $request->phone,
	        'password' 		=> bcrypt($request->password),
	        'user_type' 	=> 'user',
	        'parent_id' 	=>$parent->id,
	        'investment_limit' 	=>10000,
	        'refcode'		=>User::generateUniqueCode($province_name)
	    ]);

	    $title="";
	    $message="You have a new member under your account.";
	    mobileNotification($parent,$title,$message);

	    if($request->hasFile('avatar')) {
	        $image1 = $request->file('avatar');
	        $name1 = $user->id.time().'.'.$image1->getClientOriginalExtension();
	        $destinationPath = public_path('/uploads/userAvatar');
	        $image1->move($destinationPath, $name1);
	        $user->avatar = $name1;
	        $user->save();
	    }
	    $user->assignRole('user');
	    $token = $user->createToken('LaravelAuthApp')->accessToken;

        if(!empty($request->device_name) && !empty($request->device_token) && !empty($request->app_version) ){
        	
    	    $DeviceInfo = DeviceInfo::create([
    	    	'user_id' =>$user->id,
    	    	'device_name' =>$request->device_name,
    	    	'device_token' =>$request->device_token,
    	    	'app_version' =>$request->app_version,
    	    	'device_unique_id' =>$request->device_unique_id,
    	    	'os' => $request->device_os,
                'rem' =>$request->device_rem,
    	    ]);
    	}
    	$user = User::find($user->id);


    	$user_referrals[0]['user_id']= $parent->id;
    	$user_referrals[0]['child_id']= $user->id;
    	$user_referrals[0]['invited_by']= $parent->id;
    	$user_referrals[0]['level']= 1;
    	$user_referrals[0]['created_at']= todayDateTime();
    	$user_referrals[0]['updated_at']= todayDateTime();
    	// papa no papa kon che  level 2
    		$parent_parent = User::where('id',$parent->parent_id)->first();
    		if(!empty($parent_parent)){
    			$user_referrals[1]['user_id']= $parent_parent->id;
    			$user_referrals[1]['child_id']= $user->id;
    			$user_referrals[1]['invited_by']= $parent->id;
    			$user_referrals[1]['level']= 2;
    			$user_referrals[1]['created_at']= todayDateTime();
    			$user_referrals[1]['updated_at']= todayDateTime();
    			// papa no papa no papa kon che  level 3
    			$parent_parent_parent = User::where('id',$parent_parent->parent_id)->first();
    			if(!empty($parent_parent_parent)){
    				$user_referrals[2]['user_id']= $parent_parent_parent->id;
    				$user_referrals[2]['child_id']= $user->id;
    				$user_referrals[2]['invited_by']= $parent->id;
    				$user_referrals[2]['level']= 3;
    				$user_referrals[2]['created_at']= todayDateTime();
    				$user_referrals[2]['updated_at']= todayDateTime();
    			}

    		}
    	if(!empty($user_referrals)){
    		$UserReferral = UserReferral::insert($user_referrals);
    	}


	    $this->response = getUserDetails($user);	   
	    return $this->sendResponse($this->response,__('api.register_successfully'),1,'',$token);
	}
     
	
	public function login(Request $request){

	    $data = [
	        'email' => $request->email,
	        'password' => $request->password
	    ];
	   
	    if (method_exists($this, 'hasTooManyLoginAttempts') &&
	        $this->hasTooManyLoginAttempts($request)) {
	        $this->fireLockoutEvent($request);
	        $seconds = $this->limiter()->availableIn(
	            $this->throttleKey($request)
	        );
	       return $this->sendError(__('Too many login attempts. Please try again in '.$seconds.' seconds.'),[],0);
	    }

	    if (auth()->attempt($data)) {

	        $this->response = getUserDetails(auth()->user());
	        $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
	        return $this->sendResponse($this->response,__('api.login_success'),1,'',$token);
	    } else {
	        $this->incrementLoginAttempts($request);
	        return $this->sendError(__('api.login_error'),[],0);
	    }
	}

	

	public function logout(Request $request){
	    $request->user()->token()->revoke();
	    return $this->sendResponse($this->response,__('api.loged_out_message'),1);	    
	}

	
}
