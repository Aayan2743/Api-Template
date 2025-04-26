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

class TwoStepVerification extends Controller
{
    //

        // use to verirify email like 2 factor verification 

        public function send_verification_code(Request $request)
        {
                     
            
                            try{
            
                                $email=Auth()->user()->email;
                              
                                if($email==null){
                                    return response()->json([
                                                'status'=>false,
                                                'message'=>'Email id Not Created'
                                            ]);
                                }
                               //  dd($email);
                                $otp = rand(100000, 999999); 
            
                             

                                $user=user::where('id',Auth()->user()->id)->update([
                                    'email_otp'=> $otp,
                                    'expired_time'=>Carbon::now()->addMinutes(5),
                                ]);
                

                                $data=[
                                    'user_data'=>Auth()->user(),
                                    'otp'=>$otp,
                                    'exp'=>Carbon::now()->addMinutes(5),
                                    'subject' => 'Two-Factor Authentication',
                                ];
                                Mail::to($email)->send(new SendOtp($data));

                              
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


        // use to verirify email like 2 factor verification do saparate controller
        public function verifyEmail(Request $request){
            $validator = Validator::make(request()->all(), [
                'email'=> 'required|email',
                'Otp'=>'required'
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
    
                if(!$usermail->email_otp){
                    return response()->json([
                        'status'=>false,
                        'message'=>'Invalid OTP',
                    ]);
                }
    
                if($usermail->email_otp && $usermail->email_otp==$request->Otp){
                   
                    try{
                        $verifiedstatus=$usermail->update([
                            'email_verified_at'=>now(),
                            'email_otp'=>null,
                            'expired_time'=>null,
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
            }
            catch(\Exception $e){
                return response()->json([
                    'status'=>false,
                    'message'=>$e->getMessage()
                ]);
            }
    
          
    
    
        }
}
