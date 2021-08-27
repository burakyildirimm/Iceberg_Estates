<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'consultant_id',
        'customer_id',
        'appointment_date',
        'appointment_type',
        'distance',
        'checkout',
        'checkin',
        'address',
    ];
}
