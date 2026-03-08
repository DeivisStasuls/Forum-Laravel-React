<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MykoobAuthService
{
    /**
     * Build Mykoob OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state): string
    {
        $authorizeUrl = config('services.mykoob.oauth.authorize_url');
        $clientId = config('services.mykoob.oauth.client_id');
        $redirectUri = config('services.mykoob.oauth.redirect_uri');
        $scopes = config('services.mykoob.oauth.scopes');

        if (! $authorizeUrl || ! $clientId || ! $redirectUri) {
            throw new RuntimeException('Mykoob OAuth is not configured yet.');
        }

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scopes,
            'state' => $state,
        ]);

        return rtrim((string) $authorizeUrl, '?').'?' . $query;
    }

    /**
     * Authenticate user through OAuth authorization code.
     *
     * @return array{mykoob_user_id:string,name:string,email:string}
     */
    public function authenticateWithAuthorizationCode(string $code): array
    {
        $tokenUrl = config('services.mykoob.oauth.token_url');
        $clientId = config('services.mykoob.oauth.client_id');
        $clientSecret = config('services.mykoob.oauth.client_secret');
        $redirectUri = config('services.mykoob.oauth.redirect_uri');
        $userinfoUrl = config('services.mykoob.oauth.userinfo_url');

        if (! $tokenUrl || ! $clientId || ! $clientSecret || ! $redirectUri || ! $userinfoUrl) {
            throw new RuntimeException('Mykoob OAuth endpoints are not fully configured.');
        }

        $tokenResponse = Http::asForm()->post((string) $tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ]);

        if (! $tokenResponse->successful()) {
            throw new RuntimeException('Failed to exchange Mykoob authorization code.');
        }

        $tokenPayload = $tokenResponse->json();
        $accessToken = data_get($tokenPayload, 'access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Mykoob token response did not contain an access token.');
        }

        $profileResponse = Http::withToken($accessToken)->get((string) $userinfoUrl);

        if (! $profileResponse->successful()) {
            throw new RuntimeException('Failed to fetch Mykoob user profile.');
        }

        $payload = $profileResponse->json();

        $mykoobUserId = data_get($payload, 'sub')
            ?? data_get($payload, 'id')
            ?? data_get($payload, 'user_id')
            ?? data_get($payload, 'user_data.user.0.user_info.user_id');

        $firstName = trim((string) (data_get($payload, 'given_name')
            ?? data_get($payload, 'name')
            ?? data_get($payload, 'user_data.user.0.user_info.user_name')
            ?? ''));
        $lastName = trim((string) (data_get($payload, 'family_name')
            ?? data_get($payload, 'user_data.user.0.user_info.user_surname')
            ?? ''));

        $name = trim($firstName.' '.$lastName);
        $email = strtolower((string) (data_get($payload, 'email') ?? ''));

        if (! is_string($mykoobUserId) && ! is_numeric($mykoobUserId)) {
            throw new RuntimeException('Mykoob profile did not include a valid user ID.');
        }

        $mykoobUserId = (string) $mykoobUserId;

        if ($name === '') {
            $name = 'Mykoob User';
        }

        if ($email === '') {
            $email = 'mykoob-'.$mykoobUserId.'@mykoob.local';
        }

        return [
            'mykoob_user_id' => $mykoobUserId,
            'name' => $name,
            'email' => $email,
        ];
    }

    /**
     * Authenticate user against Mykoob and return profile basics.
     *
     * @return array{mykoob_user_id:string,name:string,email:string}
     */
    public function authenticate(string $email, string $password): array
    {
        $authResponse = Http::asForm()->post(config('services.mykoob.auth_url'), [
            'use_oauth_proxy' => 1,
            'client' => config('services.mykoob.client'),
            'username' => $email,
            'password' => $password,
        ]);

        if (! $authResponse->successful()) {
            throw new RuntimeException('Unable to reach Mykoob authentication service.');
        }

        $authPayload = $authResponse->json();
        $authError = data_get($authPayload, 'error.message');
        $accessToken = data_get($authPayload, 'access_token');

        if ($authError) {
            throw new RuntimeException((string) $authError);
        }

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Mykoob authentication did not return an access token.');
        }

        $profileResponse = Http::asForm()->post(config('services.mykoob.resource_url'), [
            'api' => 'user_data',
            'access_token' => $accessToken,
        ]);

        if (! $profileResponse->successful()) {
            throw new RuntimeException('Authenticated with Mykoob but failed to load profile.');
        }

        $profilePayload = $profileResponse->json();
        $profileError = data_get($profilePayload, 'error.message');
        if ($profileError) {
            throw new RuntimeException((string) $profileError);
        }

        $mykoobUserId = data_get($profilePayload, 'user_data.user.0.user_info.user_id');
        $firstName = trim((string) data_get($profilePayload, 'user_data.user.0.user_info.user_name', ''));
        $lastName = trim((string) data_get($profilePayload, 'user_data.user.0.user_info.user_surname', ''));
        $name = trim($firstName.' '.$lastName);

        if (! is_string($mykoobUserId) && ! is_numeric($mykoobUserId)) {
            throw new RuntimeException('Mykoob profile did not include a valid user ID.');
        }

        if ($name === '') {
            $name = 'Mykoob User';
        }

        return [
            'mykoob_user_id' => (string) $mykoobUserId,
            'name' => $name,
            'email' => strtolower($email),
        ];
    }
}
