<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Contact;

use Validator;

class ContactController extends Controller
{
    /**
     * Create a new ContactController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => []]);
    }


    /**
     *  Create a new contact.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'c_name'            => 'required|string|max:100',
            'c_surname'         => 'required|string|max:100',
            'c_type'            => 'required|string|between:1,4',
            'email'             => 'required|string|email|max:100|unique:users',
            'phone'             => 'required|string|between:8,20',
        ]);
        $validator->sometimes('address', 'required|string|between:4,10', function($input) {
            return $input->c_type == 0;
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try { 
            $contact = Contact::create(array_merge($validator->validated(), 
            [
                'consultant_id' => auth()->user()->id
            ]));   
        } catch(\Illuminate\Database\QueryException $ex){ 
            return response()->json(['errorInfo' => 'This contact is already registered.']);
        }

        return response()->json($contact);
    }


    /**
     *  Update the contact.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'                => 'required|integer',
            'c_name'            => 'required|string|max:100',
            'c_surname'         => 'required|string|max:100',
            'c_type'            => 'required|string|between:1,4',
            'email'             => 'required|string|email|max:100|unique:users',
            'phone'             => 'required|string|between:8,20',
        ]);
        $validator->sometimes('address', 'required|string|between:4,10', function($input) {
            return $input->c_type == 0;
        });

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try { 
            $contact = Contact::find($request->id)->update(array_merge($validator->validated(), 
            [
                'consultant_id' => auth()->user()->id,
            ]));
        } catch(\Illuminate\Database\QueryException $ex){ 
            return response()->json(["errorInfo" => "Doesn't find the contact or this mail is already taken."]);
        }

        return response()->json($contact);
    }


    /**
     *  Delete the contact.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'    =>  'required|integer',
        ]);
        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        $contact=Contact::where(
        [
            'id'            => $request->id, 
            'consultant_id' => auth()->user()->id
        ])->delete();
    
        $status = $contact == 1 ? "success" : "failed";

        return response()->json(["status" => $status]);
    }

}
