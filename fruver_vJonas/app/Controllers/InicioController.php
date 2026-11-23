<?php

namespace App\Controllers;

class InicioController extends BaseController
{

    public function index()
    {
        return view('inicio/v_principal_inicio');
    }
}
