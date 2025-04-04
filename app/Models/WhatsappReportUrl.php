<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappReportUrl extends Model
{
    use HasFactory;
    protected $fillable = [
        'number', 'report_id','whatsapp_web_url','whatsapp_mobile_url'
    ];
}
