<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // ambil role dari query, kalau kosong/set salah → pakai default BIDDER
        $role = $request->get('role');

        if (! in_array($role, ['ADMIN', 'BIDDER'])) {
            $role = 'BIDDER';
            // optional: kalau mau, merge supaya withQueryString juga ikut
            $request->merge(['role' => $role]);
        }

        $verification = $request->get('verification'); // verified / unverified
        $status       = $request->get('status');       // ACTIVE / INACTIVE / ''
        $search       = $request->get('search');
        $perPage      = $request->integer('per', 10);

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->where('role', $role);

        if ($verification === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verification === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        if ($status === 'ACTIVE') {
            $query->where('status', 'ACTIVE');
        } elseif ($status === 'SUSPENDED') {
            $query->where('status', 'SUSPENDED');
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $roles = [
            'ADMIN'  => 'Admin',
            'BIDDER' => 'Bidder',
        ];

        // STATISTIK PENGGUNA
        $userStats = [
            'admin'  => User::where('role', 'ADMIN')->count(),
            'bidder' => User::where('role', 'BIDDER')->count(),
        ];

        return view('admin.users.index', [
            'users'        => $users,
            'roles'        => $roles,
            'currentRole'  => $role,
            'verification' => $verification,
            'status'       => $status,
            'perPage'      => $perPage,
            'userStats'    => $userStats, 
        ]);
    }

    /** Detail pengguna */
    public function show(User $user)
    {
        $bidLots = collect();

        if ($user->isBidder()) {
            $user->load([
                'bidderProfile',
                'bidderProfile.bids.lot',
            ]);

            $profile = $user->bidderProfile;

            if ($profile) {
                $allBids = $profile->bids()
                    ->with(['lot.product'])   // load lot + product-nya
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get();

                $bidLots = $allBids
                    ->groupBy('lot_id')
                    ->map(function ($bidsForLot) {
                        $sorted = $bidsForLot
                            ->sortByDesc('created_at')
                            ->sortByDesc('id')
                            ->values();

                        $latest = $sorted->first();
                        $lot    = $latest->lot;

                        return [
                            'lot'           => $lot,
                            'latest'        => $latest,
                            'previous_bids' => $sorted->slice(1),
                        ];
                    })
                    ->sortByDesc(fn ($row) => $row['latest']->created_at)
                    ->take(5)
                    ->values();
            }
        }

        return view('admin.users.show', [
            'user'    => $user,
            'bidLots' => $bidLots,
        ]);
    }

    /** Tambah admin baru (dengan konfirmasi password admin yang login) */
    public function storeAdmin(Request $request)
    {
        // Hanya superadmin yang boleh membuat admin baru
        if (! $request->user()->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang dapat menambah admin.');
        }

        // pakai Validator manual supaya bisa tambahkan flag 'modal'
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'username'       => 'nullable|string|max:50|unique:users,username',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'admin_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'create-admin');
        }

        $data = $validator->validated();
        $currentUser = $request->user();

        // cek password superadmin
        if (! Hash::check($data['admin_password'], $currentUser->password)) {
            return back()
                ->withErrors(['admin_password' => 'Password superadmin tidak sesuai.'])
                ->withInput()
                ->with('modal', 'create-admin');
        }

        $user = new User();
        $user->name     = $data['name'];
        $user->username = $data['username'] ?? null;
        $user->email    = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->role     = 'ADMIN';
        $user->status   = 'ACTIVE';
        $user->email_verified_at = now();
        $user->save();

        return back()->with('success', "Admin baru (#{$user->id}) berhasil dibuat.");
    }

    /** Nonaktifkan / aktifkan user */
    public function toggleStatus(Request $request, User $user)
    {
        $currentUser = $request->user();

        // tidak boleh ubah status diri sendiri
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        // kalau target SUPERADMIN → jangan izinkan siapa pun sentuh
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Status akun superadmin tidak dapat diubah.');
        }

        // kalau target ADMIN → hanya SUPERADMIN yang boleh
        if ($user->isAdmin() && ! $currentUser->isSuperAdmin()) {
            return back()->with('error', 'Hanya superadmin yang dapat mengubah status admin.');
        }

        // === LOGIKA UTAMA ===
        if ($user->status === 'ACTIVE') {
            // Manual suspend dari panel:
            // - untuk bidder: bisa oleh ADMIN & SUPERADMIN
            // - untuk admin: hanya SUPERADMIN (sudah dicek di atas)
            $user->status = 'SUSPENDED';
            // NULL = suspend tanpa batas waktu (sampai diaktifkan manual)
            $user->suspended_until = null;
            $user->suspend_reason  = 'Ditangguhkan manual oleh ' . ($currentUser->role ?? 'ADMIN') . ' #' . $currentUser->id;

            $message = "Akun #{$user->id} ditangguhkan. Akun akan tetap nonaktif sampai diaktifkan kembali secara manual.";
        } else {
            // Aktifkan kembali (baik suspend otomatis karena tidak bayar,
            // maupun suspend manual dari panel)
            $user->status = 'ACTIVE';
            $user->suspended_until = null;
            $user->suspend_reason  = null;

            $message = "Akun #{$user->id} diaktifkan kembali.";
        }

        $user->save();

        return back()->with('success', $message);
    }

    public function suspendWithReason(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menangguhkan akun Anda sendiri.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Akun superadmin tidak dapat ditangguhkan.');
        }

        if ($user->isAdmin() && ! $currentUser->isSuperAdmin()) {
            return back()->with('error', 'Hanya superadmin yang dapat menangguhkan admin.');
        }

        $request->validate([
            'suspend_reason' => 'required|string|max:500',
        ]);

        $user->status = 'SUSPENDED';
        $user->suspended_until = null;
        $user->suspend_reason = $request->suspend_reason;
        $user->save();

        return back()->with('success', 'Akun berhasil ditangguhkan.');
    }

    /** RESET PASSWORD ADMIN oleh SUPERADMIN via modal */
    public function sendPasswordReset(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) abort(403);
        if ($user->role !== 'ADMIN') {
            return back()->with('error', 'Reset password dari panel admin hanya untuk akun ADMIN.');
        }
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat mereset password Anda sendiri dari sini.');
        }

        // Validator manual supaya bisa “paksa” modal tetap kebuka saat gagal
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->letters()->numbers(),
            ],
            'admin_password' => [
                'required',
                'string',
            ],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('modal', 'reset-admin')           // ✅ tanda modal reset harus kebuka
                ->with('reset_user_id', $user->id);     // ✅ biar tahu modal untuk user siapa
        }

        $data = $validator->validated();

        // cek password superadmin benar
        if (! Hash::check($data['admin_password'], $currentUser->password)) {
            return back()
                ->withErrors(['admin_password' => 'Password superadmin tidak sesuai.'])
                ->withInput()
                ->with('modal', 'reset-admin')
                ->with('reset_user_id', $user->id);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', "Password untuk admin {$user->email} berhasil direset.");
    }

    /** KIRIM ULANG VERIFIKASI (sama seperti sebelumnya) */
    public function resendVerification(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('info', 'Email pengguna ini sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "Email verifikasi telah dikirim ulang ke {$user->email}.");
    }
}
