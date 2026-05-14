<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    protected $table = 'attendances';

    protected $fillable = ['student_id', 'date', 'time_in', 'time_out', 'status','longitude','latitude'];

    protected $casts = [
        'status' => AttendanceStatus::class,
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
