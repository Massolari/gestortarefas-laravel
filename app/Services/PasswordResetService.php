<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\PasswordResetModel;

class PasswordResetService
{
    /**
     * Generate a unique reset token for password reset
     * 
     * @param string $email The user's email address
     * @return string The generated reset token
     */
    public function generateResetToken(string $email): string
    {
        // Generate an 8 character random token
        $token = Str::random(8);

        // Save the token to password resets table
        PasswordResetModel::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now()
        ]);

        return $token;
    }

    /**
     * Send reset password email with link to user
     *
     * @param string $email The recipient email address
     * @param string $token The reset token to send
     * @return void
     */
    public function sendResetPasswordEmail(
        string $email, 
        string $token
    ): void {
        try {
            \Log::info("Iniciando envio de email para: " . $email . " com token: " . $token);
            
            Mail::to($email)->send(new PasswordResetMail($token));
            
            \Log::info("Email enviado com sucesso para: " . $email);
        } catch (\Exception $e) {
            \Log::error("Erro ao enviar email: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Check if the email was already sent
     *
     * @param string $email The user's email address
     * @return bool
     */
    public function checkIfEmailWasSent(string $email): bool
    {
        return PasswordResetModel::where('email', $email)->exists();
    }
}
