<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;

class AppointmentController extends ClientController
{
    /**
     * Create a new AppointmentController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => []]);
    }


    /**
     * Create an appointment. 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $free_time = Carbon::now('Europe/London')->addHour()->addHour();

        $validator = Validator::make($request->all(), [
            'address'           => 'required|string|between:4,10',
            'c_name'            => 'required|string|max:100',
            'c_surname'         => 'required|string|max:100',
            'email'             => 'required|string|email|max:100|unique:users',
            'phone'             => 'required|string|between:8,20',
            'appointment_date'  => 'required|date_format:Y-m-d H:i:s|after_or_equal:'. $free_time,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $origin_postcode = config('constants.origin');
        $destination_postcode = $request->address;
        $postcodes = [$origin_postcode, $destination_postcode];

        $addresses = $this->obtainAddresses($postcodes)->result;
        $origin = $addresses[0]->result;
        $destination = $addresses[1]->result;

        $origin = $origin->ced. ' '. $origin->country;
        $destination = $destination->ced. ' '. $destination->country;

        $matrixdistance = config('constants.distancematrix_api');
        $distance = $this->obtainDistance($matrixdistance, $origin, $destination);

        return response()->json($distance);
    }

    
    /**
     * Update an appointment.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        return response()->json('updated.');
    }


    /**
     * Delete an appointment.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        return response()->json('deleted.');
    }

}
