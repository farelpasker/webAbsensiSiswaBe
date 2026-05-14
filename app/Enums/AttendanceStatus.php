<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case TIDAK_HADIR = 'tidak hadir';
    case HADIR = 'hadir';
    case TELAT = 'telat';
    case LIBUR = 'libur';
    case SAKIT = 'sakit';
    case IZIN = 'izin';
}
