<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;




class ManagerController extends Controller
{
    //
    public function store(Request $req){
     
        $validator=Validator::make($req->all(),[
            'name'=>'required|string|min:2|max:100',
            'contact'=>'required|digits:10|unique:users,contact',
            'gender'=>'required|in:Male,Female,Others',
            'joiningdate' => 'required|date',   
            'emergancyContactNo_1'=>'required|digits:10',
            'emergancyContactName_1'=>'required',
            'emergancyContactNo_2'=>'required|digits:10',
            'emergancyContactName_2'=>'required',
            'address'=>'required|string',
            'state'=>'required|string',
            'city'=>'required|string',
            'country'=>'required|string',
            'postalCode'=>'required|digits:6',
            'document_type'=>'required|string',
            'document_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_back' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'email' => 'nullable|email',
            'password' => 'required|min:6|confirmed',
           
        ],[
            'gender.in'=>'Please select Male Or Female Or Others'
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
        $unique_hostal_id=  Auth('api')->user()->unique_hostal_id;

        $documentFrontPath = $req->file('document_front')->store('documents', 'public');
        $documentBackPath = $req->file('document_back')->store('documents', 'public');

            $managercrearte=User::create([
                'name'=>$req->name,
                'email'=>$req->email,
                'contact'=>$req->contact,
                'password'=>Hash::make($req->password),
                'role'=>'Manager',
                'gender'=>$req->gender,
                'joiningDate'=>$req->joiningdate,
                'emergencyContactName'=>$req->emergancyContactName_1,
                'emergencyContactNo'=>$req->emergancyContactNo_1,
                'emergencyContactName2'=>$req->emergancyContactName_2,
                'emergencyContactNo2'=>$req->emergancyContactNo_2,
                'currenttAddress'=>$req->address,
                'postalcode'=>$req->postalCode,   
                'state'=>$req->state,
                'city'=>$req->city,
                'country'=>$req->country,
                // 'profile_pic'=>$req->gender,
                'document_type'=>$req->document_type,
                'document_front'=>$documentFrontPath,
                'document_back'=>$documentBackPath,
                'unique_hostal_id'=>$unique_hostal_id,
            ]);

            return response()->json([
                'status'=>true,
                'message' => 'Manager registered successfully',
                
                
            ]);
        
        }catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message' =>$e->getMessage(),
                
                
            ]);
        }



    }


    public function viewmanager($id=null){
        $unique_hostal_id=Auth()->user()->unique_hostal_id;
        if ($id) {
            // $manager = user::find($id);
          
            $managers=User::where('role','Manager')->where('unique_hostal_id',$unique_hostal_id)
            ->where('deleted_at','0')
            ->where('id',$id)
            ->get();


    
            if (!$managers) {
                return response()->json([
                    'status' => false,
                    'message' => 'Manager not found',
                ], 200);
            }
    
            return response()->json([
                'status' => true,
                'manager' => $managers,
            ], 200);
        }

        $managers=User::where('role','Manager')->where('unique_hostal_id',$unique_hostal_id)
        ->where('deleted_at','0')
        ->get();



        return response()->json([
            'status'=>true,
            'manager'=>$managers
        ]);
    
      
    }

    public function updatemanager(Request $req,$id)
    {
            //check the manager id belog to correct user

            $unique_hostal_id=Auth()->user()->unique_hostal_id;

            $manager =user::where('id',$id)->where('unique_hostal_id',$unique_hostal_id)
            ->where('deleted_at',0)
            ->where('role','Manager')->first();
            
            if(empty($manager)) {
                return response()->json([
                    'status'=> false,
                    'message'=>'Given Id not belongs to your hostal or invalid user id'
                ]);
            }

            $validator=Validator::make($req->all(),[
                'name'=>'required|string|min:2|max:100',
                // 'contact'=>'required|digits:10|unique:users,contact',
                'contact' => 'required|digits:10|unique:users,contact,' . $manager->id,
                'gender'=>'required|in:Male,Female,Others',
                'joiningdate' => 'required|date',   
                'emergancyContactNo_1'=>'required|digits:10',
                'emergancyContactName_1'=>'required',
                'emergancyContactNo_2'=>'required|digits:10',
                'emergancyContactName_2'=>'required',
                'address'=>'required|string',
                'state'=>'required|string',
                'city'=>'required|string',
                'country'=>'required|string',
                'postalCode'=>'required|digits:6',
                'document_type'=>'required|string',
                'document_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'document_back' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'email' => 'nullable|email|unique:users,email,' . $manager->id,
                'password' => 'nullable|min:6',
               
            ],[
                'gender.in'=>'Please select Male Or Female Or Others'
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
            $unique_hostal_id=  Auth('api')->user()->unique_hostal_id;
    
            $documentFrontPath = $req->file('document_front')->store('documents', 'public');
            $documentBackPath = $req->file('document_back')->store('documents', 'public');
    
            if ($req->hasFile('document_front')) {
                if ($manager->document_front) {
                    Storage::disk('public')->delete($manager->document_front);
                }
                $documentFrontPath = $req->file('document_front')->store('documents', 'public');
            } else {
                $documentFrontPath = $manager->document_front;
            }

            if ($req->hasFile('document_back')) {
                if ($manager->document_back) {
                    Storage::disk('public')->delete($manager->document_back);
                }
                $documentBackPath = $req->file('document_back')->store('documents', 'public');
            } else {
                $documentBackPath = $manager->document_back;
            }


               
              $manager->update([
                    'name'=>$req->name,
                    'email' => $req->filled('email') ? $req->email : $manager->email,
                    'contact'=>$req->contact,
                    'password' => $req->filled('password') ? Hash::make($req->password): $manager->password,
                    // 'password'=>Hash::make($req->password),
                    'role'=>'Manager',
                    'gender'=>$req->gender,
                    'joiningDate'=>$req->joiningdate,
                    'emergencyContactName'=>$req->emergancyContactName_1,
                    'emergencyContactNo'=>$req->emergancyContactNo_1,
                    'emergencyContactName2'=>$req->emergancyContactName_2,
                    'emergencyContactNo2'=>$req->emergancyContactNo_2,
                    'currenttAddress'=>$req->address,
                    'postalcode'=>$req->postalCode,   
                    'state'=>$req->state,
                    'city'=>$req->city,
                    'country'=>$req->country,
                    // 'profile_pic'=>$req->gender,
                    'document_type'=>$req->document_type,
                    'document_front'=>$documentFrontPath,
                    'document_back'=>$documentBackPath,
                    'unique_hostal_id'=>$unique_hostal_id,

                ]);
            
                // $managercrearte=User::create([
                //     'name'=>$req->name,
                //     'email'=>$req->email,
                //     'contact'=>$req->contact,
                //     'password'=>Hash::make($req->password),
                //     'role'=>'Manager',
                //     'gender'=>$req->gender,
                //     'joiningDate'=>$req->joiningdate,
                //     'emergencyContactName'=>$req->emergancyContactName_1,
                //     'emergencyContactNo'=>$req->emergancyContactNo_1,
                //     'emergencyContactName2'=>$req->emergancyContactName_2,
                //     'emergencyContactNo2'=>$req->emergancyContactNo_2,
                //     'currenttAddress'=>$req->address,
                //     'postalcode'=>$req->postalCode,   
                //     'state'=>$req->state,
                //     'city'=>$req->city,
                //     'country'=>$req->country,
                //     // 'profile_pic'=>$req->gender,
                //     'document_type'=>$req->document_type,
                //     'document_front'=>$documentFrontPath,
                //     'document_back'=>$documentBackPath,
                //     'unique_hostal_id'=>$unique_hostal_id,
                // ]);
    
                return response()->json([
                    'status'=>true,
                    'message' => 'Manager updated successfully',
                    
                    
                ]);
            
            }catch(\Exception $e){
                return response()->json([
                    'status'=>false,
                    'message' =>$e->getMessage(),
                    
                    
                ]);
            }

            
    }

    public function deletemanager($id){

        $unique_hostal_id=Auth()->user()->unique_hostal_id;


        $manager_id = $id;

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
                'message' => 'This manager does not belong to your user.',
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
                'message' => 'This manager is already assigned to a building so unable to delete.',
            ]);
        }
        


        $manager->update([
              'deleted_at'=>'1'
        ]);











        // $manager =user::where('id',$id)->where('unique_hostal_id',$unique_hostal_id)->where('role','Manager')->update([
        //     'deleted_at'=>'1'
        // ]);
        
        if(!$manager) {
            return response()->json([
                'status'=> false,
                'message'=>'Given Id not belongs to your hostal or invalid user id'
            ]);
        }else{
            return response()->json([
                'status'=> true,
                'message'=>'manager deleted successfully...'
            ]);
        }
        
    }
  


}


