<?php

namespace App\Libraries;

class AuthRouteMethods
{
    /**
     * Register the typical authentication routes for an application.
     *
     * @param array $options
     * @return callable
     */
    public function adminAuth()
    {
        return function ($options = []) {
            $this->group([], function () use ($options) {
                // Login Routes...
                $this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
                $this->post('login', 'Auth\LoginController@login');
                // Logout Routes...
                $this->post('logout', 'Auth\LoginController@logout')->name('logout');
                // Password Reset Routes...
                $this->resetPassword();
                // Password Confirmation Routes...
                $this->confirmPassword();
                // Email Verification Routes...
                $this->emailVerification();
            });
        };
    }

    /**
     * Register the typical reset password routes for an application.
     *
     * @return callable
     */
    public function resetPassword()
    {
        return function () {
            $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
            $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
            $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
            $this->post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
        };
    }

    /**
     * Register the typical confirm password routes for an application.
     *
     * @return callable
     */
    public function confirmPassword()
    {
        return function () {
            $this->get('password/confirm', 'Auth\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
            $this->post('password/confirm', 'Auth\ConfirmPasswordController@confirm');
        };
    }

    /**
     * Register the typical email verification routes for an application.
     *
     * @return callable
     */
    public function emailVerification()
    {
        return function () {
            $this->get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
            $this->get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->name('verification.verify');
            $this->post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
        };
    }
}
