<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordResetModel;
use App\Models\UserModel;
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
        $token = Str::random(8);

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
    public function sendResetPasswordMail(string $email): void
    {
        $this->deleteResetToken($email);
        $token = $this->generateResetToken($email);
        $this->sendMail($email, $token);
    }

    /**
     * Send the reset password email
     *
     * @param string $email The recipient email address
     * @param string $token The reset token to send
     * @return void
     */
    public function sendMail(string $email, string $token): void
    {
        Mail::to($email)->send(new PasswordResetMail($token));
    }

    /**
     * Delete the reset token from the database
     *
     * @param string $email The user's email address
     * @return void
     */
    public function deleteResetToken(string $email): void
    {
        PasswordResetModel::where('email', $email)->delete();
    }

    /**
     * Check if the user exists
     *
     * @param string $email The user's email address
     * @return bool
     */
    public function userExists(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }

    /**
     * Update the user's password
     *
     * @param string $token The reset token
     * @param string $password The new password
     * @return void
     */
    public function updatePassword(string $token, string $password): void
    {
        $userEmail = PasswordResetModel::where('token', $token)->first()->email;
        UserModel::where('email', $userEmail)->update([
            'password' => Hash::make($password)
        ]);

        $this->deleteResetToken($userEmail);
    }

    /**
     * Check if the token is expired or invalid
     *
     * @param string $email The user's email address
     * @return bool
     */
    public function tokenIsExpiredOrInvalid(string $email): bool
    {
        $resetRecord = PasswordResetModel::where('email', $email)->first();
        
        if (
            !$resetRecord
            || $resetRecord->created_at < now()->subHours(1)
        ) {
            return true;
        }
        
        return false;
    }
}
