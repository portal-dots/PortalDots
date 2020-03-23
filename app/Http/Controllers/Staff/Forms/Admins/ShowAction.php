<?php

namespace App\Http\Controllers\Staff\Forms\Admins;

use App\Eloquents\Form;
use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(Form $form)
    {
        $admins = $form->admins;
        
        return view('v2.staff.forms.admins.index')
            ->with('form', $form)
            ->with('admins', $admins);
    }
}
