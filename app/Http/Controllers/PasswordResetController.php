<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
    public function sendResetPasswordMail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        if (!$this->passwordResetService->userExists($request->email)) {
            return redirect()->route('password.reset')
                ->withErrors('Usuário não encontrado')
                ->with('email', $request->email)
                ->withInput(['email' => $request->email]);
        }
        
        $this->passwordResetService->sendResetPasswordMail($request->email);

        return redirect()
            ->route('password.reset')
            ->withSuccess('E-mail de redefinição enviado com sucesso.')
            ->with('email', $request->email)
            ->with('button_label', 'Enviar novamente')
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

        // if ($this->passwordResetService->tokenIsExpiredOrInvalid($request->token)) {
        //     return redirect()->route('password.reset', [
        //         'error' => 'Token inválido ou expirado'
        //     ]);
        // }

        $this->passwordResetService->updatePassword($request->token, $request->password);

        return redirect()->route('login', [
            'success' => 'Senha atualizada com sucesso'
        ]);
    }
}
