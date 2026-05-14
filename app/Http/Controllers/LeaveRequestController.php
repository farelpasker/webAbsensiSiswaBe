<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Repositories\AttendanceRepository;
use App\Repositories\LeaveRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    private $repo;
    private $attendanceRepo;

    public function __construct(LeaveRequestRepository $repo, AttendanceRepository $attendanceRepo)
    {
        $this->repo = $repo;
        $this->attendanceRepo = $attendanceRepo;
    }

    public function index(Request $request) {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'date' => 'nullable|date',
        ]);

        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $search = $request->search;
            $date = $request->date;

            $data = $this->repo->paginate($page, $perPage, $search, $date);
            return ApiResponse::Paginate(
                $data->items(),
                "Daftar permintaan izin berhasil diambil",
                PaginationHelper::meta($data)
            );
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function show($id) {
        try {
            $leaveRequest = $this->repo->findById($id);
            if (!$leaveRequest) {
                return ApiResponse::Custom(false, 'Permintaan izin tidak ditemukan', null, 404);
            }
            return ApiResponse::Create("Data permintaan izin berhasil diambil", $leaveRequest);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $request->validate([
            'type' => 'required|in:izin,sakit',
            'reason' => 'required|string|max:255',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ],[
            'type.required' => 'Tipe izin harus diisi',
            'type.in' => 'Tipe izin harus berupa "izin" atau "sakit"',
            'reason.required' => 'Alasan izin harus diisi',
            'reason.string' => 'Alasan izin harus berupa teks',
            'reason.max' => 'Alasan izin tidak boleh lebih dari 255 karakter',
            'proof.file' => 'Bukti harus berupa file',
            'proof.mimes' => 'Bukti harus berupa file dengan format jpg, jpeg, png, atau pdf',
            'proof.max' => 'Bukti tidak boleh lebih dari 2MB',
            'start_date.required' => 'Tanggal mulai izin harus diisi',
            'start_date.date' => 'Tanggal mulai izin harus berupa tanggal yang valid',
            'end_date.required' => 'Tanggal selesai izin harus diisi',
            'end_date.date' => 'Tanggal selesai izin harus berupa tanggal yang valid',
            'end_date.after_or_equal' => 'Tanggal selesai izin harus sama dengan atau setelah tanggal mulai izin',
        ]);

        $student = auth()->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }

        $already = $this->repo->studentHasPendingRequest($student->id, $request->start_date);
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
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

            DB::commit();
            return ApiResponse::Create('Permintaan izin berhasil diajukan', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function approve($id) {
        DB::beginTransaction();
        try {
            $leaveRequest = $this->repo->findById($id);
            if (!$leaveRequest) {
                return ApiResponse::Custom(false, 'Permintaan izin tidak ditemukan', null, 404);
            }
            if ($leaveRequest->status !== 'pending') {
                return ApiResponse::Custom(false, 'Permintaan izin sudah diproses', null, 400);
            }

            $this->repo->update($id, ['status' => 'approved']);

            $start = \Carbon\Carbon::parse($leaveRequest->start_date);
            $end = \Carbon\Carbon::parse($leaveRequest->end_date);

            while ($start <= $end) {
                $this->attendanceRepo->create([
                    'student_id' => $leaveRequest->student_id,
                    'date' => $start->toDateString(),
                    'time_in' => '00:00:00', 
                    'time_out' => null,
                    'status' => $leaveRequest->type,
                ]);
                $start->addDay();
            }
            DB::commit();
            return ApiResponse::Create('Permintaan izin berhasil disetujui', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function reject($id) {
        DB::beginTransaction();
        try {
            $leaveRequest = $this->repo->findById($id);
            if (!$leaveRequest) {
                return ApiResponse::Custom(false, 'Permintaan izin tidak ditemukan', null, 404);
            }
            if ($leaveRequest->status !== 'pending') {
                return ApiResponse::Custom(false, 'Permintaan izin sudah diproses', null, 400);
            }

            DB::commit();
            $this->repo->update($id, ['status' => 'rejected']);
            return ApiResponse::Create('Permintaan izin berhasil ditolak', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function myLeaveRequests(Request $request) {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $student = auth()->user()->student;
            if (!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;

            $data = $this->repo->paginateByStudent($student->id, $page, $perPage);
            return ApiResponse::Paginate(
                $data->items(),
                "Daftar permintaan izin berhasil diambil",
                PaginationHelper::meta($data)
            );
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }
}
