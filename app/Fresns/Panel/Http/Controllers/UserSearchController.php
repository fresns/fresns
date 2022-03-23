<?php

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
            $users = User::where('username', 'like', '%'.$keyword.'%')
                ->orWhere('nickname', 'like', '%'.$keyword.'%')
                ->paginate();
        }

        return response()->json($users);
    }
}
