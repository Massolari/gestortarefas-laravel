<?php

namespace App\Http\Controllers;

use App\Services\SignUpService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignUpController extends Controller
{
    public function __construct(
        private SignUpService $signUpService
    ){}

    /**
     * Display the initial signup email form
     *
     * @return \Illuminate\View\View
     */
    public function signUp(): View
    {
        return view('pages.signup.email');
    }

    /**
     * Display the signup form
     *
     * @return \Illuminate\View\View
     */
    public function signUpForm(): View
    {
        return view('pages.signup.form');
    }

    /**
     * Display the verification code input form
     *
     * @return \Illuminate\View\View
     */
    public function verifyCodeForm(): View
    {
        return view('pages.signup.verify');
    }

    /**
     * Handle email verification code sending
     *
     * @param Request $request HTTP request containing email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendVerificationCode(Request $request): RedirectResponse|View
    {
        $request->validate(
            ['email' => 'required|email:rfc,dns'],
            ['email.*' => 'E-mail inválido']
        );

        if ($this->signUpService->checkEmailExists($request->email)) {
            return view('pages.signup.email', [
                'error' => 'E-mail já cadastrado',
                'email' => $request->email
            ]);
        }

        session(['email' => $request->email]);
        
        $code = $this->signUpService->generateAndSaveVerificationCode($request->email);
        $this->signUpService->sendVerificationEmail($request->email, $code);

        return redirect()->route('signup.verify');
    }

    /**
     * Verify the submitted verification code
     *
     * @param Request $request HTTP request containing verification code
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate(
            ['code' => 'required|string|size:6'],
            ['code.*' => 'Código de verificação inválido']
        );

        $verification = $this->signUpService->verifyCode(session('email'), $request->code);

        if (!$verification) {
            return redirect()->back()->with([
                'error' => 'Código de verificação inválido ou expirado',
            ]);
        }

        $verification->update(['verified' => true]);

        return redirect()->route('signup.form');
    }

    /**
     * Handle the signup form submission
     *
     * @param Request $request HTTP request containing user registration data
     * @return \Illuminate\Http\RedirectResponse
     */
    public function signUpSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|min:3',
            'password' => 'required|min:6|same:password_confirm',
            'password_confirm' => 'required|min:6',
        ], [
            'name.min' => 'O nome deve ter no mínimo 3 caracteres',
            'name.required' => 'O nome é obrigatório',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres',
            'password.same' => 'As senhas não conferem',
            'password.required' => 'A senha é obrigatória',
            'password_confirm.min' => 'A senha de confirmação deve ter no mínimo 6 caracteres',
            'password_confirm.same' => 'As senhas não conferem',
            'password_confirm.required' => 'A senha de confirmação é obrigatória',
        ]);        

        $this->signUpService->createUser($request->all());
        $verification = $this->signUpService->checkVerification($request->email);
        $verification->delete();

        return redirect()->route('login')
            ->with('success', 'Cadastro realizado com sucesso');
    }
}
