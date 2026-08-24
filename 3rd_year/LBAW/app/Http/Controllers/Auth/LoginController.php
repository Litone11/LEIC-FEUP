<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    /**
     * Show the login form.
     *
     * If the user is already authenticated, redirect them
     * to the cards dashboard instead of showing the form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('dashboard');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Process an authentication attempt.
     *
     * Validates the incoming request, checks the provided
     * credentials, and logs the user in if successful.
     * The session is regenerated to protect against session fixation.
     */
   public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Prevent blocked users from signing in
            if ($user->isBlocked()) {
                $reason = $user->activeBlockReason();
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => $reason
                        ? 'A sua conta está bloqueada: ' . $reason
                        : 'A sua conta está bloqueada.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Decide destination by role
            $destinationRoute = $user->isAdmin()
                ? 'admin.dashboard'
                : 'dashboard';

            // Always go to the dashboard (no intended)
            return redirect()
                ->route($destinationRoute)
                ->with('success', 'Sessão iniciada com sucesso.');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas. Confirma o email e a palavra-passe.',
        ])->onlyInput('email');
    }
}
