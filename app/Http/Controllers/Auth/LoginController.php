<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Añadido para mejor legibilidad
use App\Models\User; // Asegúrate de importar tu modelo User

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function login(Request $request)
    {
        $this->validateLogin($request);

        // 1. Identificar si es email o username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2. Buscar al usuario primero para verificar su estado
        $user = User::where($loginType, $request->login)->first();

        if ($user) {
            // 3. Si el usuario existe pero está bloqueado (status = false/0)
            if (!$user->status) {
                return back()
                    ->withErrors(['login' => 'Tu cuenta se encuentra bloqueada. Contacte al administrador.'])
                    ->withInput($request->only('login', 'remember'));
            }
        }

        // 4. Intentar el login incluyendo la condición de status activo
        $credentials = [
            $loginType => $request->login, 
            'password' => $request->password,
            'status'   => true // Solo permite entrar si el status en la DB es 1
        ];

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectPath());
        }

        // Si falla (clave incorrecta o usuario no existe)
        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);
    }
}