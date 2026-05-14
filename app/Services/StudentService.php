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

    public function updateStudent($id, $data)
    {
        return DB::transaction(function() use ($id, $data) {
            $student = $this->studentrepo->findById($id);
            $user = $student->user;

            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
            ];

            // Hanya update email jika berbeda (untuk menghindari unique constraint)
            if ($data['email'] !== $user->email) {
                $updateData['email'] = $data['email'];
            }

            if (!empty($data['password'])) {
                $updateData['password'] = bcrypt($data['password']);
            }

            $this->userrepo->update($user->id, $updateData);

            $studentUpdateData = [
                'nis' => $data['nis'],
                'kelas_id' => $data['kelas_id'],
                'parent_id' => $data['parent_id'] ?? null,
            ];

            return $this->studentrepo->update($id, $studentUpdateData);
        });
    }

    public function deleteStudent($id)
    {
        return DB::transaction(function() use ($id) {
            $student = $this->studentrepo->findById($id);
            $userId = $student->user_id;

            $this->studentrepo->delete($id);
            return $this->userrepo->delete($userId);
        });
    }
}