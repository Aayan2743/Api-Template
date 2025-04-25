<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtp;
use Carbon\Carbon;



class CustomApiAuthController extends Controller
{
    //


    public function register(Request $req){
        // dd($req->all());
        $validator=Validator::make($req->all(),[
            'name'=>'required|string|min:2|max:100',
            'email'=>'required|string|email|max:100|unique:users',
            'password'=>'required|min:6',
            'contact'=>'required|digits:10|unique:users,contact'
        ]);
    
        if($validator->fails()){
            return response()->json(
                [
                    'status'=>false,
                    'message'=> $validator->errors()->first(),
                ],
               200);
        }
    
        try{
            $code = substr(str_replace('.', '', microtime(true)), -8);
            $userresult=User::create([
                'name'=>$req->name,
                'email'=>$req->email,
                'password'=>Hash::make($req->password),
                'contact'=>$req->contact,
                'unique_hostal_id'=>$code,
                'role'=>'Admin',
            ]);

            return response()->json([
                'status'=>true,
                'message' => 'user registered successfully',
                'user'=>$userresult
                
            ]);
        
        }catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message' =>$e->getMessage(),
                
                
            ]);
        }

       
       
    }

    // login with mobile and email
    public function login(Request $req){

        $validator=Validator::make($req->all(),[
            'username'=>'required|string',
            'password'=>'required'
        ]);

        if($validator->fails()){
            return response()->json(
                [
                    'status'=>false,
                    'message'=> $validator->errors()->first(),
                ],200);
            
        }
        $loginField = filter_var($req->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'contact';

        $credentials = [
            $loginField => $req->username,
            'password' => $req->password
        ];

        try{

            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Username or Password',
                ], 200);
            }

          

            $user = auth()->user();
            $role = auth()->user()->role;
            $refreshToken = Str::random(60); // Laravel helper for secure random string

            $user->refresh_token = hash('sha256', $refreshToken);
            $user->save();

            return response()->json([
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'role'=>$role,
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        
            // return $this->resondedJwtToken($token);
        }catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ],200);
        }
    


    }


     public function logout(){
      
        auth('api')->logout();


        return response()->json([
            'status'=>true,
            'message'=>'User Successfully Loggout'],200);
    }

    public function reset_password_link(Request $request){
              
        $validator=validator::make(request()->all(), [
                    'email'=>'required|email'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status'=>false,
                        'message'=> $validator->errors()->first(),
                    ],200);
                }

                try{

              

                $emailCheck=user::where('email','=', $request->email)
                ->first();

                if(!$emailCheck){
                    return response()->json([
                        'status'=>false,
                        'message'=>'Email id Not Registered'
                    ]);
                }

                if($emailCheck->deleted_at==1){
                    return response()->json([
                        'status'=>false,
                        'message'=>'Email id deleted'
                    ]);
                }

               
                    $otp = rand(100000, 999999); 

                    $emailCheck->update([
                        'email_otp'=> $otp,
                        'expired_time'=>Carbon::now()->addMinutes(5),
                    ]);
                   
    
                    $data=[
                        'user_data'=>$emailCheck,
                        'otp'=>$otp,
                        'exp'=>Carbon::now()->addMinutes(5),
                        'subject' => 'OTP for Verification - ' . now()->format('H:i:s'),
                    ];
                    Mail::to($request->email)->send(new SendOtp($data));

                    return response()->json([
                        'status'=>true,
                        'message'=>'OTP send to your email id'
                    ]);

                  }
                  catch(\Exception $e){
                    return response()->json([
                        'status'=>false,
                        'message'=>$e->getMessage()
                    ]);
                  }

              


    }
    
    public function verify_otp_update_password(Request $request){
        $validator = Validator::make(request()->all(), [
            'email'=> 'required|email',
            'Otp'=>'required',
            'password'=>'required|min:6'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=>$validator->errors()->first(),
            ]);
        }

        try{

            $usermail=user::where('email',$request->email)->first();

            if(!$usermail){
                return response()->json([
                    'status'=>false,
                    'message'=>'Email id not registered',
                ]);
            }

            if($usermail->deleted_at==1){
                return response()->json([
                    'status'=>false,
                    'message'=>'Email id deleted',
                ]);
            }

            if($usermail->expired_time && now()->gt($usermail->expired_time)){
                // The expiration time is set and has already passed

                return response()->json([
                    'status'=>false,
                    'message'=>'Opt Expired',
                ]);
            }

            // if(!$usermail->email_otp){
            //     return response()->json([
            //         'status'=>false,
            //         'message'=>'Invalid OTP',
            //     ]);
            // }

            if($usermail->email_otp && $usermail->email_otp==$request->Otp){
               
                try{
                    $verifiedstatus=$usermail->update([
                   
                        'email_otp'=>null,
                        'expired_time'=>null,
                        'password'=>Hash::make($request->password),
                    ]);
    
                    if($verifiedstatus){
                        return response()->json([
                            'status'=>true,
                            'message'=>'OTP verified Successfully'
                        ]);
                    }else{
                        return response()->json([
                            'status'=>false,
                            'message'=>'Some thing went wrong please try again'
                        ]);
                    }

                }catch(\Exception $e){
                    return response()->json([
                        'status'=>false,
                        'message'=>$e->getMessage()
                    ]);
                }
               
               
           

            }
            else{
                return response()->json([
                            'status'=>false,
                            'message'=>'Invalid OTP',
                        ]);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage()
            ]);
        }

      


    }








   









    
}
