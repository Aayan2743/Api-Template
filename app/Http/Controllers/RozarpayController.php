<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\rozarpaypayment;
use Razorpay\Api\Api;
class RozarpayController extends Controller
{
    //

    public function createOrder(Request $request)
    {
       
    
        // $checkkeys=rozarpaypayment::where('saas_id',Auth()->user()->unique_hostal_id)->first();
        $checkkeys=rozarpaypayment::where('saas_id','13027083')->first();

        if(!$checkkeys){
            return response()->json([
                'status'=>false,
                'message'=>'Rozarpay Keys not updated'
            ]);
        }
       
        try{
            // $api = new Api($checkkeys->RAZORPAY_KEY,$checkkeys->RAZORPAY_SECRET);
            $api = new Api('rzp_test_zn3zcQle0s179F','WpBiTrxTyaSOdhxWTYnMmFlN');

            $order = $api->order->create([
                'receipt' => uniqid(),
                'amount' => $request->amount * 100, // Amount in paise (so multiply by 100)
                'currency' => 'INR',
            ]);
        
            return response()->json([
                // 'order_id' => $order['id'],
                // 'amount' => $order['amount']/100,
                // 'currency' => $order['currency'],

                'order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'key' => $checkkeys->RAZORPAY_KEY, // frontend needs your public key
            ]);

           


        }catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=> $e->getMessage()
            ]);
        }

       
    }

    
    public function verifyPayment(Request $request)
        {
            // $checkkeys=rozarpaypayment::where('saas_id',Auth()->user()->unique_hostal_id)->first();
            $checkkeys=rozarpaypayment::where('saas_id','13027083')->first();


            if(!$checkkeys){
                return response()->json([
                    'status'=>false,
                    'message'=>'Rozarpay Keys not updated'
                ]);
            }    

            // $api = new Api($checkkeys->RAZORPAY_KEY,$checkkeys->RAZORPAY_SECRET);
            $api = new Api('rzp_test_zn3zcQle0s179F','WpBiTrxTyaSOdhxWTYnMmFlN');


            try {
                $attributes = [
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature
                ];

                $api->utility->verifyPaymentSignature($attributes);

                $payment = $api->payment->fetch($request->razorpay_payment_id); 


               
                // dd(  $payment);
                $order = $api->order->fetch($request->razorpay_order_id); 

            //      dd([
            //     'payment_details' => $payment,
            //     'order_details' => $order
            // ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Payment Verified Successfully!',
                    'details'=>$payment
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' =>  $e->getMessage(),
                   
                ]);
            }
    }




}
