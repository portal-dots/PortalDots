<?php

namespace App\Http\Controllers\Staff\Forms\Admins;

use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowAction extends Controller
{
    public function __invoke(Form $form)
    {
        // TODO: ログインユーザーが所有者出ない場合は個人設定にリダイレクトする
        $admins = $form->admins;
        
        return view('v2.staff.forms.admins.index')
            ->with('form', $form)
            ->with('admins', $admins);
    }
}
