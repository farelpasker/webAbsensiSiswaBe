<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class StudentService
{
    private $studentrepo;
    private $userrepo;

    public function __construct(StudentRepository $studentrepo, UserRepository $userrepo)
    {
        $this->studentrepo = $studentrepo;
        $this->userrepo = $userrepo;
    }

    public function verifyFaceDescriptor($student, $faceDescriptor)
    {
        $storedDescriptor = json_decode($student->face_descriptor, true);

        $incomingDescriptor = $faceDescriptor;

        $distance = $this->calculateEuclideanDistance($storedDescriptor, $incomingDescriptor);

        $isMatch = $distance < 0.6;

        return [
            "match" => $isMatch,
            "distance" => round($distance, 4)
        ];
    }

    private function calculateEuclideanDistance($vec1, $vec2)
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Descriptor wajah tidak valid');
        }

        $sum = 0;
        foreach ($vec1 as $i => $value) {
            $difference = $value - $vec2[$i];
            $sum += $difference * $difference;
        }

        return sqrt($sum);
    }

    public function createStudent($data)
    {
        return DB::transaction(function() use ($data) {
            $user = $this->userrepo->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $data['phone'] ?? null,
            ]);

            $user->assignRole('student');

            $student = $this->studentrepo->create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'kelas_id' => $data['kelas_id'],
                'parent_id' => $data['parent_id'] ?? null,
            ]);

            return $student;
        });
    }
}