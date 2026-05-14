<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherClassroom extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'teacher_classrooms';
    protected $fillable = ['teacher_id', 'kelas_id', 'subject'];

    public function teacher()
    {
        return $this->belongsTo(User::class,'teacher_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
