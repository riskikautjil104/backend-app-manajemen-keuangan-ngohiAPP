<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query()->where('is_admin', false)->orderByDesc('id');

        if ($search = $request->query('search')) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $users = $q->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }
}
