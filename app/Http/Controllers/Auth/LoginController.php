<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\CoreBridgeAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, CoreBridgeAuthService $auth, AuditLogger $audit)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = $auth->attempt($credentials['login'], $credentials['password']);

        if (! $user) {
            $audit->record('login_failed', null, null, ['description' => $auth->failureReason()], $request);

            return back()
                ->withErrors(['login' => $auth->failureReason() ?? 'Login gagal.'])
                ->onlyInput('login');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('dosen_farmasi.available_roles', $auth->availableRoles());
        $audit->record('login_success', $user, $user, [], $request);

        if (count($auth->availableRoles()) > 1) {
            $request->session()->put('dosen_farmasi.role_selected', false);

            return redirect()->route('role.select');
        }

        $request->session()->put('dosen_farmasi.role_selected', true);

        return redirect()->intended($user->isAdmin() ? route('filament.admin.pages.admin-dashboard') : route('dosen.dashboard'));
    }

    public function selectRole(Request $request)
    {
        $roles = $request->session()->get('dosen_farmasi.available_roles', [$request->user()?->role]);

        return view('auth.select-role', ['roles' => array_values(array_filter($roles))]);
    }

    public function storeRole(Request $request, AuditLogger $audit)
    {
        $roles = $request->session()->get('dosen_farmasi.available_roles', [$request->user()->role]);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', $roles)],
        ]);

        $request->user()->update(['role' => $data['role']]);
        $request->session()->put('dosen_farmasi.role_selected', true);
        $audit->record('role_selected', $request->user(), $request->user(), ['role' => $data['role']], $request);

        return redirect($data['role'] === 'admin' ? route('filament.admin.pages.admin-dashboard') : route('dosen.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
