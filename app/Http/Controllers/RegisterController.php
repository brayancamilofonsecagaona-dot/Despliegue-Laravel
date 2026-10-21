<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create()
    {
        return view('register');
    }

    public function store(RegisterRequest $request)
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')), // hash seguro, nunca texto plano (CA-01.7)
        ]);

        return redirect()
            ->route('register')
            ->with('status', '¡Cuenta creada con éxito! Ya puedes iniciar sesión.'); // CA-01.6
    }
}