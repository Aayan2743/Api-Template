<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Validator;
use App\Models\User;
use App\Models\msg91;

class Mgs91Controller extends Controller
{
    //
    public function SendOtp(Request $request)   {
        // dd($request->phone);
        $validator=Validator::make($request->all(),[
            'phone'=>'required|digits:10',
            
        ]);

            if ($validator->fails()){
                return response()->json([
                    'status'=>false,
                    'message'=> $validator->errors()->first(),
                ]);
            }

            try{
                $checkmobileExist=User::where('contact', $request->phone)->first();
              
                if($checkmobileExist){

                    if($checkmobileExist->deleted_at==1){
                        return response()->json([
                            'status'=>false,
                            'message'=>'User Deleted',
                        ]);
                    }

                    
                    $checkMsg91=msg91::where('company_id',Auth()->user()->unique_hostal_id)->first();

                   
                    if($checkMsg91){
                        $authKey = $checkMsg91->authKey; // keep your key in .env
                        $senderId =$checkMsg91->senderId;
                        $templateId = $checkMsg91->templateId; // required for transactional messages
                        
                        try{
                            $response = Http::post('https://control.msg91.com/api/v5/otp', [
                                'authkey' => $authKey,
                                'template_id' => $templateId,
                                'mobile' => '91' . $request->phone,
                                'sender' => $senderId,
                            ]);
                        
                            $responseData = $response->json();
                            if (isset($responseData['type']) && $responseData['type'] === 'success') {
                                // OTP sent successfully
                            } else {
                                // Log and handle the error
                                // Log::error('MSG91 OTP Send Failed', $responseData);
                                return response()->json(['error' => $responseData['message'] ?? 'Failed to send OTP'], 400);
                            }


                        }catch (\Exception $e){
                            return response()->json([
                                'status'=>false,
                                'message'=>$e->getMessage(),
                            ]);
                        }
                
                      
                    }else{
                        return response()->json([
                            'status'=>false,
                            'message'=>'Mgs91 Not Integrated',
                        ]);
                    }

                   
                }else{
                    return response()->json([
                        'status'=>false,
                        'message'=>'Invalid Mobile Number',
                    ]);
                }
                
            }catch(\Exception $e){
                return response()->json([
                    'status'=>false,
                    'message'=> $e->getMessage(),
                ]);
            }
     
       
     
    }

    public function VerifyOtp(Request $request) {

        $validator=Validator::make($request->all(),[
            'phone'=>'required|digits:10',
            'otp'=>'required'

        ]);
        if ($validator->fails()){
            return response()->json([
                'status'=>false,
                'message'=> $validator->errors()->first(),
            ]);
        }

        $checkMsg91=msg91::where('company_id',Auth()->user()->company_id)->first();
        if($checkMsg91){
     
            $authKey = $checkMsg91->authKey;
     
            try{
                $response = Http::get('https://control.msg91.com/api/v5/otp/verify', [
                    'authkey' => $authKey,
                    'mobile' => '91' . $request->phone,
                    'otp' => $request->otp,
                ]);
            
                if (isset($response['type']) && $response['type'] == 'success') {
                    // OTP verified
                    return response()->json([
                        'status'=>true,
                        'message'=> 'success',
                    ]);
        
                } else {
                    // OTP invalid
        
                    return response()->json([
                        'status'=>false,
                        'message'=> $response['message'],
                    ]);
        
                }

            }catch(\Exception $e){
                return response()->json([
                    'status'=>false,
                    'message'=> $e->getMessage(),
                ]);
            }
           
        }else{
            return response()->json([
                'status'=>false,
                'message'=> 'Mgs91 Not Integrated',
            ]);
        }
       
      
    }
}
