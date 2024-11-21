<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetModel;
use App\Services\PasswordResetService;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * Show reset password form
     *
     * @return View
     */
    public function resetPasswordForm(): View
    {
        return view('pages.passwordReset.email');
    }

    /**
     * Send reset password email
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendResetPasswordEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = UserModel::where('email', $request->email)->first();

        PasswordResetModel::where('email', $user->email)->delete();

        if (!$user) {
            return redirect()->route('password.reset')
                ->withErrors('Usuário não encontrado')
                ->with('email', $request->email)
                ->withInput(['email' => $request->email]);
        }
        
        $token = $this->passwordResetService->generateResetToken($user->email);
        $this->passwordResetService->sendResetPasswordEmail($user->email, $token);

        return redirect()
            ->route('password.reset')
            ->withSuccess('E-mail de redefinição enviado com sucesso.')
            ->with('email', $request->email)
            ->withInput(['email' => $request->email]);
    }

    /**
     * Show new password form
     *
     * @return View
     */
    public function newPasswordForm(): View
    {
        return view('pages.passwordReset.new_password_form');
    }

    /**
     * Update user password
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'token' => 'required'
        ]);

        $passwordReset = PasswordResetModel::where('token', $request->token)
            ->where('created_at', '>', now()->subHour())
            ->first();

        if (!$passwordReset) {
            return redirect()->route('password.reset', [
                'error' => 'Token inválido ou expirado'
            ]);
        }

        $user = UserModel::where('email', $passwordReset->email)->first();

        if (!$user) {
            return redirect()->route('password.reset', [
                'error' => 'Usuário não encontrado'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        PasswordResetModel::where('email', $passwordReset->email)->delete();

        return redirect()->route('login', [
            'success' => 'Senha atualizada com sucesso'
        ]);
    }
    /**
     * Resend reset password email
     *
     * @param string $email
     * @return RedirectResponse
     */
    public function resendResetPasswordEmail(string $email): RedirectResponse
    {
        PasswordResetModel::where('email', $email)->delete();

        $token = $this->passwordResetService->generateResetToken($email);
        $this->passwordResetService->sendResetPasswordEmail($email, $token);

        return redirect()->route('password.reset')
            ->withSuccess('E-mail de redefinição enviado com sucesso')
            ->with('email', $email);
    }
}
