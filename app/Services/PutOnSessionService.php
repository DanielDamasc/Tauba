<?php

namespace App\Services;



class PutOnSessionService
{
    public function putOnSession($request): void
    {
        $request->session()->put('tipo', $request->input('tipo'));
        $request->session()->put('variaveis', $request->input('variaveis'));
        $request->session()->put('restricoes', $request->input('restricoes'));
        $request->session()->put('z', $request->input('z'));
    }
}
