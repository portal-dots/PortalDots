<?php

namespace App\Http\Requests\Forms\Admins;

use Illuminate\Foundation\Http\FormRequest;
use App\User;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'admin_ids' => ['nullable'],
        ];
    }

    public function withValidator($validator)
    {
        $non_staff_users = [];

        $admin_ids = str_replace(["\r\n", "\r", "\n"], "\n", $this->admin_ids);
        ;
        $admin_ids = explode("\n", $admin_ids);
        $admin_ids = array_filter($admin_ids);

        $admins = User::getByStudentIdIn($admin_ids);

        foreach ($admins as $admin) {
            $admin_ids = array_diff($admin_ids, [$admin->student_id]);
            if ($admin->is_staff === false) {
                $non_staff_users[] = $admin;
            }
        }

        $validator->after(function ($validator) use ($admin_ids, $non_staff_users) {
            if (!empty($admin_ids)) {
                $validator->errors()->add('admin_ids', implode(' ', $admin_ids) . 'は未登録です');
            }

            if (!empty($non_staff_users)) {
                $validator->errors()->add('admin_ids', implode(' ', $non_staff_users) . 'はスタッフではありません');
            }
        });
    }
}
