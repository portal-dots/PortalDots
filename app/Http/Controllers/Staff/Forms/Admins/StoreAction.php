<?php

namespace App\Http\Controllers\Staff\Forms\Admins;

use App\Eloquents\User;
use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Forms\Admins\StoreRequest;
use Illuminate\Support\Facades\Auth;

class StoreAction extends Controller
{
    public function __invoke(Form $form, StoreRequest $request)
    {
        $admin_ids = str_replace(["\r\n", "\r", "\n"], "\n", $this->admin_ids);
        ;
        $admin_ids = explode("\n", $admin_ids);
        $admin_ids = array_filter($admin_ids);

        $admins = User::getByStudentIdIn($admin_ids);

        $admins = $admins->filter(function ($value, $key) {
            return $this->id !== Auth::id();
        });

        $form->admins()->detach();

        foreach ($admins as $admin) {
            $admin->adminForms()->attach();
        }
    }
}
