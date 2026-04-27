<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    // register
    public function userRegister($req)
    {
        return User::create([
            "username" => $req->username,
            "name" => $req->name,
            "email" => $req->email,
            "type" => $req->type ?? 'public',
            "password" => Hash::make($req->password),
        ]);
    }

    // login
    public function userLogin($req)
    {
        $user = User::where("username", $req->username_or_email)->orWhere("email", $req->username_or_email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            throw ValidationException::withMessages([
                "login_or_password" => ["Login yoki parol xato!"],
            ]);
        }

        return $user;
    }

    //edit profile
    public function userUpdate($req)
    {
        $user = auth()->user();
        $data = $req->only("username", "name", "email", "type");

        if ($req->filled("password")) {
            $data["password"] = Hash::make($req->password);
        }

        $user->update($data);
        return $user;
    }

    // logout
    public function userLogout($req)
    {
        $req->user()->currentAccessToken()->delete();
    }

    // delete account
    public function userDelete($req)
    {
        $req->user()->delete();
    }
}
