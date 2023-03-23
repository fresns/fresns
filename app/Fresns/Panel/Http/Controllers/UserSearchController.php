<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->keyword;

        $users = [];
        if ($keyword) {
            $users = User::where('username', 'like', "%$keyword%")->orWhere('nickname', 'like', "%$keyword%")->paginate();
        }

        return response()->json($users);
    }
}
