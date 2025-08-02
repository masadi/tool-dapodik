<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use Socialite;

class AuthController extends Controller
{
    public function login(Request $r, $provider){
        $validator = Validator::make($r->all(), [
            'code' => 'nullable|string',
            'hash' => 'nullable|string',
            'otp' => 'nullable|numeric',
            'token' => 'nullable|string',
            'secret' => 'nullable|string',

        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Incorrect Data Posted',
                'status' => 445,
                'error' => TRUE,
            ];
        }

        $hash = $r->hash ?? null;
        $hashuser = Cache::get($hash);
        $message = NULL;
        if ($hashuser) {
            return $this->SocialSignupNext($r, $hashuser);
        }
        try {
            // Socialite will pick response data automatic
            $user = Socialite::driver($provider)->stateless()->user();
            $token = $user->token ?? null;
            $refreshToken = $user->refreshToken ?? null;
            $expiresIn = $user->expiresIn ?? null;
            $tokenSecret = $user->tokenSecret ?? null;
            $id = $user->id ?? $user->getId();
            $nickname = $user->nickname ?? $user->getNickname();
            $firstName = $user->name->firstName ?? null;
            $lastName = $user->name->lastName ?? null;
            $name = $user->name ?? $firstName . ' ' . $lastName ?? null;
            $email = $user->getEmail();
            $profileImage = $user->getAvatar();

             $data =  [
                'name' => $name,
                'nickname' => $nickname,
                'profileImage' => $profileImage,
                'username' => '',
                'email' => $email,
                'provider' => $provider,
                'provider_id' => $id,
                'token' => $token,
                'tokenSecret' => $tokenSecret,
                'refreshToken' => $refreshToken,
                'expiresIn' => $expiresIn,

            ];
        // this is optional can be skip you can return your user data from here
        if($email){
            if(Str::contains($email, 'belajar.id')){
                return $this->SocialSignupNext($r, $data);
            } else {
                return [
                    'message' => 'Email terdeteksi tidak menggunakan akun BELAJAR.ID',
                    'error' => TRUE,
                ];
            }
        }

        } catch (\Throwable $th) {
            logger($th);
            $message = $th->getMessage();
        }

        return [
            'message' => $message,
            'status' => 445,
            'error' => TRUE,
        ];
    }
    public function SocialSignupNext($request, $userdata)
    {
        $email = $userdata['email'];
        $provider = $userdata['provider'];
        $provider_id = $userdata['provider_id'];
        $name = $userdata['name'];
        $usr = User::where('email', $email)->get();

        $user =  $usr->where('provider', $provider)
            ->where('provider_id', $provider_id)
            ->first();

        if ($user) {
            return $this->SocialLogin($request, $user);
        }
        $user = $usr->first();
        if ($user) {
            /*$user->update([

                'provider' => $provider,
                'provider_id' => $provider_id,

            ]);*/
            return $this->SocialLogin($request, $user);
        }
        $u =  User::create([
            'name' => $name,
            'email' => $email,
            //'provider' => $provider,
            //'provider_id' => $provider_id,
            'password' => (12345678),

        ]);
        // this is optional can be skip you can return your user data from here
        return $this->SocialLogin($request, $u);
    }
    public function SocialLogin($r, $user)
    {

        $hashid =  Str::random(12);
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
        $userAbility = [
            [
                'action' => 'read',
                'subject' => 'Web'
            ],
        ];
        return [
            'error' => FALSE,
            'userAbility' => $userAbility,
            'accessToken' =>  $token,
            'userData' => $user,
        ];
        // to verify additional security
        if ($user->google2fa_secret && !$this->mlean($r->otp)) {
            Cache::put($hashid, $user, now()->addMinutes(15));
            return [
                'message' => 'Unauthorized',
                'status' => 444,
                'hash' => $hashid
            ];
        }
        // check 2fa
        if ($this->mlean($r->otp)) {
            $g = \Google2FA::verifyKeyNewer(
                $user->google2fa_secret,
                ($this->mlean($r->otp)),
                $user->google2fa_ts
            );
            if (!$g) {
                return [
                    'message' => '2FA Expired Or Incorrect Code',
                    'status' => 445
                ];
            } else {
                $user->update([

                    'google2fa_ts' => $g

                ]);
                // optional incase you are using passport oAuth
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                return [
                    'u' => [
                        'data' => $tokenResult->accessToken,
                        'user' => $user
                    ]
                ];
            }
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        return [
            'u' => [
                        'data' => $tokenResult->accessToken,
                        'user' => $user
                    ]
        ];
    }
}
