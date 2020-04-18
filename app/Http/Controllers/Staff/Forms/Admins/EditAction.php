<?php

namespace App\Http\Controllers\Staff\Forms\Admins;

use App\Eloquents\Form;
use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke(Form $form, User $user)
    {
        // ログインユーザーが所有者であるかもここでビューに渡す
        // もしくはポリシーで対応
        return view('v2.staff.forms.admins.edit')
            ->with('form', $form)
            ->with('user', $user);
    }
}
