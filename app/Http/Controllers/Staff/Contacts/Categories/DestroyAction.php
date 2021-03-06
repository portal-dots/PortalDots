<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DestroyAction extends Controller
{
    public function __invoke(ContactCategory $category)
    {
        $category->delete();

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', 'メールアドレスを削除しました');
    }
}
