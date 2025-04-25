<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\building;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class buildingController extends Controller
{
    //

    public function store(Request $request){
        $validator=validator::make($request->all(),[
            'name'=>'required',
            'phone'=>'required|digits:10|unique:buildings,phone',
            'manager_id'=>'required',
            'full_address'=>'required',
            'floors'=>'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'=>false,
                'message'=> $validator->errors()->first(),
            ]);
        }



        $manager_id = $request->input('manager_id');

        try
        {
            $manager_id = $request->input('manager_id');

            $manager = user::where('id', $manager_id)->first();

            if (!$manager) {
                return response()->json([
                    'status' => false,
                    'message' => 'Manager not found.',
                ]);
            }

            if ($manager->deleted_at != 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'This manager has been deleted.',
                ]);
            }
            
            if ($manager->unique_hostal_id != auth()->user()->unique_hostal_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'This manager does not belong to your hostel.',
                ]);
            }
            
            if ($manager->role !== 'Manager') {
                return response()->json([
                    'status' => false,
                    'message' => 'The selected user is not a manager.',
                ]);
            }
            
            if (!is_null($manager->building_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'This manager is already assigned to a building.',
                ]);
            }
            


           
            DB::beginTransaction();
            $add_building=building::create([
                'name'=> $request->input('name'),
                'phone'=> $request->input('phone'),
                'manager_id'=> $request->input('manager_id'),
                'full_address'=> $request->input('full_address'),
                'floors'=> $request->input('floors'),
                'unique_hostal_id'=> Auth()->user()->unique_hostal_id,
            ]);

            $update_in_manage_table=user::where('id',$manager_id)->update([
                'building_id'=> $add_building->id,
            ]);
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'Building created and manager updated successfully.',
            ]);

         }
            catch(\Exception $e){
                DB::rollBack();
                return response()->json([
                    'status'=>false,
                    'message'=>$e->getMessage(),
                ]);
                
            }
      
    }
}
