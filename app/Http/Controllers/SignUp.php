<?php

namespace App\Http\Controllers;

use App\Services\SignUpService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignUp extends Controller
{
    protected $signUpService;

    /**
     * Constructor - injects SignUpService dependency
     * 
     * @param SignUpService $signUpService Service for handling signup operations
     */
    public function __construct(SignUpService $signUpService)
    {
        $this->signUpService = $signUpService;
    }

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
     * Handle email verification code sending
     *
     * @param Request $request HTTP request containing email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendVerificationCode(Request $request): RedirectResponse|View
    {
        $request->validate(['email' => 'required|email']);

        if ($this->signUpService->checkEmailExists($request->email)) {
            return view('pages.signup.email', [
                'error' => 'E-mail já cadastrado',
                'email' => $request->email
            ]);
        }

        $code = $this->signUpService->generateAndSaveVerificationCode($request->get('email'));
        $this->signUpService->sendVerificationEmail($request->get('email'), $code);

        return redirect()->route('signup.verify')->with('email', $request->email);
    }

    /**
     * Display verification code input form
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function verifyCodeForm(): RedirectResponse|View
    {
        if (!session('email')) {
            return redirect()->route('signup');
        }

        return view('pages.signup.verify');
    }

    /**
     * Verify the submitted verification code
     *
     * @param Request $request HTTP request containing verification code
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $verification = $this->signUpService->verifyCode($request->email, $request->code);

        if (!$verification) {
            return view('pages.signup.verify', [
                'error' => 'Código de verificação inválido ou expirado',
                'email' => $email
            ]);
        }

        $verification->update(['verified' => true]);

        return redirect()->route('signup.form')
            ->with('email', $request->email);
    }

    /**
     * Display the signup form after email verification
     *
     * @return View
     */
    public function signUpForm()
    {
        if (!session('email')) {
            return redirect()->route('signup');
        }

        return view('pages.signup.form', [
            'email' => session('email'),
        ]);
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
            'email' => 'required|email',
            'password' => 'required|min:6|same:password_confirm',
            'password_confirm' => 'required|min:6',
        ]);

        $verification = $this->signUpService->checkVerification($request->email);

        if (!$verification) {
            return redirect()->route('signup');
        }

        $this->signUpService->createUser($request->all());
        $verification->delete();

        return redirect()->route('login')
            ->with('success', 'Cadastro realizado com sucesso');
    }
}
