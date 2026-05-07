<?php

namespace App\Services;

use App\Repositories\StudentRepository;

class StudentService
{
    private $studentrepo;

    public function __construct(StudentRepository $studentrepo)
    {
        $this->studentrepo = $studentrepo;
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
}