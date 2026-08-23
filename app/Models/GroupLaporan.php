<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupLaporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'dokumen',
        'file',
        'verify',
        'inserted_by',
        'updated_by',
        'deleted_by',
    ];
}
