<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\setting;

class RozarPayAddOn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try{
           
            if(setting::where("id",3)->value('status')==0) {
                return response()->json([
                 "status"=> false,
                 'message'=>'Rozarpay Not Enabled'
                ]);
             }
                                            


             return $next($request);
        }catch(\Exception $e){
            return response()->json([
                "status"=> false,
                'message'=>$e->getMessage()
               ]); 
        } 

    }
}
