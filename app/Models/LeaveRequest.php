<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'leave_requests';
    protected $fillable = ['student_id', 'type', 'reason', 'proof', 'start_date', 'end_date', 'status'];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    protected function proof(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                return url('storage/' . $value);
            }
        );
    }
}
