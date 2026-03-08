<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MykoobAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class MykoobSessionController extends Controller
{
    public function create(Request $request, MykoobAuthService $mykoob): Response
    {
        try {
            $state = Str::random(40);
            $request->session()->put('mykoob_oauth_state', $state);
            $redirectUrl = $mykoob->getAuthorizationUrl($state);
        } catch (RuntimeException) {
            $redirectUrl = config('services.mykoob.login_url');
        }

        if (request()->header('X-Inertia')) {
            return Inertia::location($redirectUrl);
        }

        return redirect()->away($redirectUrl);
    }

    public function callback(Request $request, MykoobAuthService $mykoob): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('mykoob_oauth_state', '');
        $incomingState = (string) $request->query('state', '');
        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDescription = (string) $request->query('error_description', '');

        if ($error !== '') {
            throw ValidationException::withMessages([
                'email' => $errorDescription !== '' ? $errorDescription : 'Mykoob login was cancelled or failed.',
            ]);
        }

        if ($expectedState === '' || $incomingState === '' || ! hash_equals($expectedState, $incomingState)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid Mykoob login state. Please try again.',
            ]);
        }

        if ($code === '') {
            throw ValidationException::withMessages([
                'email' => 'Mykoob did not return an authorization code.',
            ]);
        }

        try {
            $profile = $mykoob->authenticateWithAuthorizationCode($code);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }

        $user = User::query()
            ->where('mykoob_user_id', $profile['mykoob_user_id'])
            ->orWhere('email', $profile['email'])
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $profile['name'],
                'email' => $profile['email'],
                'mykoob_user_id' => $profile['mykoob_user_id'],
                'password' => Str::random(40),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'name' => $user->name ?: $profile['name'],
                'email' => $user->email ?: $profile['email'],
                'mykoob_user_id' => $user->mykoob_user_id ?: $profile['mykoob_user_id'],
            ]);
        }

        if ($user->isBanned()) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been banned.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('forum.index', absolute: false));
    }
}
