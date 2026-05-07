<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes, HasUuids;
    protected $table = 'kelas';

    protected $fillable = ['nama'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
