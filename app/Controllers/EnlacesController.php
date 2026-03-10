<?php
namespace App\Controllers;

class EnlacesController extends BaseController
{
    public function __construct()
    {
        $this->requireEmpresa();
    }

    public function index()
    {
        $this->render('enlaces/index', [
            'title' => 'Enlaces de interés',
        ]);
    }
}
