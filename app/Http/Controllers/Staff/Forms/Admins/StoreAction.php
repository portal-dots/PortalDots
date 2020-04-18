<?php

namespace App\Http\Controllers\Staff\Forms\Admins;

use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\FormAdministrator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Forms\Admins\StoreRequest;

class StoreAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(Form $form, StoreRequest $request)
    {
        $form_admins = $form->admins()->get();

        $admin_ids = str_replace(["\r\n", "\r", "\n"], "\n", $request->admin_ids);

        $admin_ids = explode("\n", $admin_ids);
        $admin_ids = array_filter($admin_ids);

        $admins = $this->user->getByStudentIdIn($admin_ids);

        $admins = $admins->filter(function ($value, $key) use ($form) {
            return FormAdministrator::where('form_id', $form->id)
                ->where('user_id', $value->id)
                ->doesntExist();
        });

        foreach ($admins as $admin) {
            $admin->adminForms()
                ->attach(
                    $form->id,
                );
        }
    }
}
