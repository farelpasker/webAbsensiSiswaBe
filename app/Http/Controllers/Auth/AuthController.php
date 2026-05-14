<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\Student;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private $service;
    private $repo;
    public function __construct(UserService $user, UserRepository $repo)
    {
        $this->service = $user;
        $this->repo = $repo;
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return ApiResponse::Custom(false, 'Email atau password salah', null, 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::Custom(true, 'Login berhasil', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'roles' => $user->getRoleNames(),
        ], 200);
    }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'password_confirmation' => 'required|string|min:8',
            'role' => 'required|in:student,parent',
            'nis' => 'required_if:role,student|unique:students',
            'kelas_id' => 'required_if:role,student|exists:kelas,id',
        ],[
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 8 karakter',
            'password_confirmation.required' => 'Konfirmasi password harus diisi',
            'password_confirmation.min' => 'Konfirmasi password minimal 8 karakter',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);
            if($user->hasRole('student')) {
                Student::create([
                    'user_id' => $user->id,
                    'nis' => $request->nis,
                    'kelas_id' => $request->kelas_id,
                ]);
            }
            DB::commit();
            return ApiResponse::Create('Registrasi berhasil',$user);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::Custom(true, 'Logout berhasil', null, 200);
    }

    public function me()
    {
        $user = auth()->user();
        $user->roles = $user->getRoleNames();
        return ApiResponse::Custom(true, 'Data pengguna berhasil diambil', $user, 200);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|confirmed|min:8',
            'new_password_confirmation' => 'required|string|min:8',
        ],[
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi',
            'new_password_confirmation.min' => 'Konfirmasi password baru minimal 8 karakter',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::Custom(false, 'Password saat ini salah', null, 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return ApiResponse::Custom(true, 'Password berhasil diubah', null, 200);
    }

    public function updateProfile(ProfileRequest $request) {
        $data = $request->validated();
        $user = auth()->user();
        $this->service->updateUser($user->id, $data);
        // Refresh user untuk mendapatkan data terbaru termasuk avatar
        $user->refresh();
        return ApiResponse::Custom(true, 'Profil berhasil diperbarui', $user, 200);
    }

    public function listUsers(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $data = $this->repo->listUsers($page, $perPage);
            return ApiResponse::Paginate($data->items(), 'Data pengguna berhasil diambil', PaginationHelper::meta($data));
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }
}
