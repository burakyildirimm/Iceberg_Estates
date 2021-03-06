<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Contact;
use App\Models\Appointment;

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
        # appointment date must be at least two hours later.
        $free_time = Carbon::now('Europe/London')->addHour()->addHour();

        $validator = Validator::make($request->all(), [
            'address'           => 'required|string|between:4,10',
            'c_name'            => 'required|string|max:100',
            'c_surname'         => 'required|string|max:100',
            'c_type'            => 'required|string|between:1,4',
            'email'             => 'required|string|email|max:100|unique:users',
            'phone'             => 'required|string|between:8,20',
            'appointment_date'  => 'required|date_format:Y-m-d H:i:s|after_or_equal:'. $free_time,
            'appointment_type'  => 'required|string|between:1,4',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $destination_postcode = $request->address;
        $calculated = $this->calculateDistance($request, $destination_postcode);
        $distance = $calculated['distance'];
        $duration = $calculated['duration'];
        $est_checkout = $calculated['est_checkout'];
        $est_checkin = $calculated['est_checkin'];


        # update or create the contact.
        if (!$request->c_type == "0") { $contact_arr = array_merge($validator->validated(), ['address' => '', 'consultant_id' => auth()->user()->id]); } 
        else { $contact_arr = array_merge($validator->validated(), ['consultant_id' => auth()->user()->id]); }
        $contact = Contact::updateOrCreate(['email' => $request->email], $contact_arr);
        

        $isFree = Appointment::whereBetween('checkout', [$est_checkout, $est_checkin])->orWhereBetween('checkin', [$est_checkout, $est_checkin])->get();

        if ( count($isFree) > 0 ) {
            return response()->json(['error: ' => 'This date is full. Please choose another date!'], 406);
        }

        # update or create the appointment.
        $appointment = Appointment::updateOrCreate([
            'consultant_id' => auth()->user()->id,
            'customer_id'   => $contact->id,
            'address'       => $request->address
        ], array_merge($validator->validated(), [
            'consultant_id' => auth()->user()->id,
            'customer_id'   => $contact->id,
            'distance'      => $distance,
            'checkout'      => $est_checkout,
            'checkin'       => $est_checkin,
        ]));

        return response()->json($appointment);
    }

    
    /**
     * Update an appointment.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        # appointment date must be at least two hours later.
        $free_time = Carbon::now('Europe/London')->addHour()->addHour();

        $validator = Validator::make($request->all(), [
            'appointment_id'    => 'required|digits_between:1,5',
            'address'           => 'required|string|between:4,10',
            'c_name'            => 'required|string|max:100',
            'c_surname'         => 'required|string|max:100',
            'c_type'            => 'required|string|between:1,4',
            'email'             => 'required|string|email|max:100|unique:users',
            'phone'             => 'required|string|between:8,20',
            'appointment_date'  => 'required|date_format:Y-m-d H:i:s|after_or_equal:'. $free_time,
            'appointment_type'  => 'required|string|between:1,4',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        # Can update only onwer this appointment!
        $find_app = Appointment::find($request->appointment_id)->where('consultant_id', auth()->user()->id)->get();
        if ( !count($find_app) > 0 ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $destination_postcode = $request->address;
        $calculated = $this->calculateDistance($request, $destination_postcode);
        $distance = $calculated['distance'];
        $duration = $calculated['duration'];
        $est_checkout = $calculated['est_checkout'];
        $est_checkin = $calculated['est_checkin'];



        # update or create the contact.
        if (!$request->c_type == "0") { $contact_arr = array_merge($validator->validated(), ['address' => '', 'consultant_id' => auth()->user()->id]); } 
        else { $contact_arr = array_merge($validator->validated(), ['consultant_id' => auth()->user()->id]); }
        $contact = Contact::updateOrCreate(['email' => $request->email], $contact_arr);
        

        $isFree = Appointment::whereBetween('checkout', [$est_checkout, $est_checkin])
                            ->orWhereBetween('checkin', [$est_checkout, $est_checkin])
                            ->where('id', '!=', $request->appointment_id)->get();

        if ( count($isFree) > 0 ) {
            return response()->json(['error: ' => 'This date is full. Please choose another date!'], 406);
        }

        # update or create the appointment.
        $appointment = Appointment::updateOrCreate([
            'id' => $request->appointment_id,
        ], array_merge($validator->validated(), [
            'consultant_id' => auth()->user()->id,
            'customer_id'   => $contact->id,
            'distance'      => $distance,
            'checkout'      => $est_checkout,
            'checkin'       => $est_checkin,
        ]));

        return response()->json($appointment);

    }


    /**
     * Delete an appointment.
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

        // $appointment = Appointment::find($request->id)->delete();
        $appointment=Appointment::where(
        [
            'id'            => $request->id, 
            'consultant_id' => auth()->user()->id
        ])->delete();

        $status = $appointment == 1 ? "success" : "failed";
        
        return response()->json(['status' => $status]);
    }


    /**
     * Calculate distance and duration.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDistance($request, $destination_postcode) {
        $origin_postcode = config('constants.origin');
        $postcodes = [$origin_postcode, $destination_postcode];

        $addresses = $this->obtainAddresses($postcodes)->result;
        $origin = $addresses[0]->result;
        $destination = $addresses[1]->result;
        $origin = $origin->ced. ' '. $origin->country;
        $destination = $destination->ced. ' '. $destination->country;

        $matrixdistance = config('constants.distancematrix_api');
        $calculations = $this->obtainCalculations($matrixdistance, $origin, $destination);
        $distance = $calculations->rows[0]->elements[0]->distance->text;
        $duration = $calculations->rows[0]->elements[0]->duration->value;

        # converted humandate to timestamp.
        $appointment_timestamp = Carbon::parse($request->appointment_date)->timestamp;
        $checkout_timestamp = $appointment_timestamp - $duration;
        $checkin_timestamp = $appointment_timestamp + $duration;
        $est_checkout = Carbon::createFromTimestampUTC($checkout_timestamp, 'Europe/London')->format('Y-m-d H:i:s');
        # appointment time added too.
        $est_checkin = Carbon::createFromTimestampUTC($checkin_timestamp, 'Europe/London')->addHour()->format('Y-m-d H:i:s');

        return ['distance' => $distance, 'duration' => $duration, 'est_checkout' => $est_checkout, 'est_checkin' => $est_checkin];
    }

}
