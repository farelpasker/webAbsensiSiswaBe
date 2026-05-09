<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Repositories\LeaveRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    private $repo;

    public function __construct(LeaveRequestRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request) {
        // Implementasi untuk menampilkan daftar permintaan izin
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $request->validate([
            'type' => 'required|in:izin,sakit',
            'reason' => 'required|string|max:255',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'date' => 'required|date',
        ],[
            'type.required' => 'Tipe izin harus diisi',
            'type.in' => 'Tipe izin harus berupa "izin" atau "sakit"',
            'reason.required' => 'Alasan izin harus diisi',
            'reason.string' => 'Alasan izin harus berupa teks',
            'reason.max' => 'Alasan izin tidak boleh lebih dari 255 karakter',
            'proof.file' => 'Bukti harus berupa file',
            'proof.mimes' => 'Bukti harus berupa file dengan format jpg, jpeg, png, atau pdf',
            'proof.max' => 'Bukti tidak boleh lebih dari 2MB',
            'date.required' => 'Tanggal izin harus diisi',
            'date.date' => 'Tanggal izin harus berupa tanggal yang valid',
        ]);

        $student = auth()->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }

        $already = $this->repo->studentHasPendingRequest($student->id, $request->date);
        if ($already) {
            return ApiResponse::Custom(false, 'Anda sudah memiliki permintaan izin yang sedang diproses untuk tanggal tersebut', null, 400);
        }

        if($request->hasFile('proof')) {
            $pathProof = $request->file('proof')->store('leave_proofs', 'public');
        } 

        $data = $this->repo->create([
            'student_id' => $student->id,
            'type' => $request->type,
            'reason' => $request->reason,
            'proof' => $pathProof ?? null,
            'date' => $request->date,
        ]);

            DB::commit();
            return ApiResponse::Create('Permintaan izin berhasil diajukan', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }
}
