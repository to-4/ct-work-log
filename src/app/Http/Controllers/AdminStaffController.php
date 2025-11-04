<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{

    /**
     * スタッフ一覧表示
     *
     */
    public function list()
    {
        $users = User::where('is_admin', false)
            ->get();

        return view('admin.staffs.list', compact('users'));
    }

}
