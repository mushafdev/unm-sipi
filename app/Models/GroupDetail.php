<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'mahasiswa_id',
        'pembekalan_id',
        'pembekalan_verify',
        'pembakelan_verify_at',
        'pembakelan_verify_by',
    ];
}
