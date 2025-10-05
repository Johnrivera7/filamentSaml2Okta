<?php

use Illuminate\Support\Facades\Route;
use JohnRiveraGonzalez\Saml2Okta\Controllers\Saml2Controller;

Route::middleware('web')->group(function () {
    // Rutas SAML2
    Route::get('/saml2/login', [Saml2Controller::class, 'redirect'])->name('saml2.login');
    Route::get('/saml2/callback', [Saml2Controller::class, 'callback'])->name('saml2.callback');
    Route::get('/auth/callback', [Saml2Controller::class, 'callback'])->name('auth.callback');
});
