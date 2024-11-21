<?php

namespace App\Services;

use App\Models\UserModel;
use App\Models\VerificationMailModel;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class SignUpService
{
    /**
     * Check if an email already exists in the users table
     *
     * @param string $email The email to check
     * @return bool Returns true if email exists, false otherwise
     */
    public function checkEmailExists(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }

    /**
     * Generate a verification code and save it to the database
     *
     * @param string $email The email to generate code for
     * @return string The generated verification code
     */
    public function generateAndSaveVerificationCode(string $email): string
    {
        $verificationCode = random_int(100000, 999999);
        
        VerificationMailModel::updateOrCreate(
            ['email' => $email],
            [
                'verification_code' => $verificationCode,
                'verified' => false,
                'expires_at' => now()->addMinutes(5)
            ]
        );

        return $verificationCode;
    }

    /**
     * Send verification email with code to user
     *
     * @param string $email The recipient email address
     * @param string $code The verification code to send
     * @return void
     */
    public function sendVerificationEmail(
        string $email, 
        string $code
    ): void {
        Mail::to($email)->send(new VerificationMail($code));
    }

    /**
     * Verify if the provided code matches and is not expired
     *
     * @param string $email The email to verify
     * @param string $code The verification code to check
     * @return VerificationMailModel|null Returns verification record if valid, null otherwise
     */
    public function verifyCode(
        string $email, 
        string $code
    ): ?VerificationMailModel {
        return VerificationMailModel::where('email', $email)
            ->where('verification_code', $code)
            ->first();
    }

    /**
     * Check if email has been verified
     *
     * @param string $email The email to check verification status
     * @return VerificationMailModel|null Returns verification record if verified, null otherwise
     */
    public function checkVerification(string $email): ?VerificationMailModel
    {
        return VerificationMailModel::where('email', $email)
            ->where('verified', true)
            ->first();
    }

    /**
     * Create a new user in the database
     *
     * @param array $userData Array containing user data (name, email, password)
     * @return UserModel The newly created user model
     */
    public function createUser(array $userData): UserModel
    {
        return UserModel::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'level' => 1,
            'experience' => 0,
            'created_count' => 0,
            'deleted_count' => 0,
            'completed_count' => 0,
            'canceled_count' => 0,
            'list_created_count' => 0,
            'list_deleted_count' => 0,
        ]);
    }
}