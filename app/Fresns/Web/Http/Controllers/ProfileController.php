<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Exceptions\ErrorException;
use App\Fresns\Web\Helpers\ApiHelper;
use App\Fresns\Web\Helpers\QueryHelper;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // posts
    public function posts(Request $request, string $uidOrUsername)
    {
        $query = $request->all();
        $query['uidOrUsername'] = $uidOrUsername;

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'posts'   => $client->getAsync('/api/v2/post/list', [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('profile.posts', compact('items', 'profile', 'posts'));
    }

    // comments
    public function comments(Request $request, string $uidOrUsername)
    {
        $query = $request->all();
        $query['uidOrUsername'] = $uidOrUsername;

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'comments'   => $client->getAsync('/api/v2/comment/list', [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('profile.comments', compact('items', 'profile', 'comments'));
    }

    // likers
    public function likers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/interactive/like", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.likers', compact('items', 'profile', 'users'));
    }

    // dislikers
    public function dislikers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/interactive/dislike", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.dislikers', compact('items', 'profile', 'users'));
    }

    // followers
    public function followers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/interactive/follow", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.followers', compact('items', 'profile', 'users'));
    }

    // followers you follow
    public function followersYouFollow(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/followers-you-follow", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.followers-you-follow', compact('items', 'profile', 'users'));
    }

    // blockers
    public function blockers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/interactive/block", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.blockers', compact('items', 'profile', 'users'));
    }

    /**
     * like.
     */

    // likeUsers
    public function likeUsers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/like/users", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.likes.users', compact('items', 'profile', 'users'));
    }

    // likeGroups
    public function likeGroups(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'groups'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/like/groups", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $results['groups']['data']['list'],
            paginate: $results['groups']['data']['paginate'],
        );

        return view('profile.likes.groups', compact('items', 'profile', 'groups'));
    }

    // likeHashtags
    public function likeHashtags(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'hashtags'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/like/hashtags", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $results['hashtags']['data']['list'],
            paginate: $results['hashtags']['data']['paginate'],
        );

        return view('profile.likes.hashtags', compact('items', 'profile', 'hashtags'));
    }

    // likePosts
    public function likePosts(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'posts'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/like/posts", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('profile.likes.posts', compact('items', 'profile', 'posts'));
    }

    // likeComments
    public function likeComments(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'comments'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/like/comments", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('profile.likes.comments', compact('items', 'profile', 'comments'));
    }

    /**
     * dislike.
     */

    // dislikeUsers
    public function dislikeUsers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/dislike/users", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.dislikes.users', compact('items', 'profile', 'users'));
    }

    // dislikeGroups
    public function dislikeGroups(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'groups'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/dislike/groups", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $results['groups']['data']['list'],
            paginate: $results['groups']['data']['paginate'],
        );

        return view('profile.dislikes.groups', compact('items', 'profile', 'groups'));
    }

    // dislikeHashtags
    public function dislikeHashtags(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'hashtags'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/dislike/hashtags", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $results['hashtags']['data']['list'],
            paginate: $results['hashtags']['data']['paginate'],
        );

        return view('profile.dislikes.hashtags', compact('items', 'profile', 'hashtags'));
    }

    // dislikePosts
    public function dislikePosts(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'posts'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/dislike/posts", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('profile.dislikes.posts', compact('items', 'profile', 'posts'));
    }

    // dislikeComments
    public function dislikeComments(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'comments'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/dislike/comments", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('profile.dislikes.comments', compact('items', 'profile', 'comments'));
    }

    /**
     * following.
     */

    // followingUsers
    public function followingUsers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/follow/users", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.following.users', compact('items', 'profile', 'users'));
    }

    // followingGroups
    public function followingGroups(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'groups'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/follow/groups", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $results['groups']['data']['list'],
            paginate: $results['groups']['data']['paginate'],
        );

        return view('profile.following.groups', compact('items', 'profile', 'groups'));
    }

    // followingHashtags
    public function followingHashtags(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'hashtags'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/follow/hashtags", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $results['hashtags']['data']['list'],
            paginate: $results['hashtags']['data']['paginate'],
        );

        return view('profile.following.hashtags', compact('items', 'profile', 'hashtags'));
    }

    // followingPosts
    public function followingPosts(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'posts'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/follow/posts", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('profile.following.posts', compact('items', 'profile', 'posts'));
    }

    // followingComments
    public function followingComments(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'comments'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/follow/comments", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('profile.following.comments', compact('items', 'profile', 'comments'));
    }

    /**
     * blocking.
     */

    // blockingUsers
    public function blockingUsers(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'users'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/block/users", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $users = QueryHelper::convertApiDataToPaginate(
            items: $results['users']['data']['list'],
            paginate: $results['users']['data']['paginate'],
        );

        return view('profile.blocking.users', compact('items', 'profile', 'users'));
    }

    // blockingGroups
    public function blockingGroups(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'groups'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/block/groups", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $groups = QueryHelper::convertApiDataToPaginate(
            items: $results['groups']['data']['list'],
            paginate: $results['groups']['data']['paginate'],
        );

        return view('profile.blocking.groups', compact('items', 'profile', 'groups'));
    }

    // blockingHashtags
    public function blockingHashtags(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'hashtags'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/block/hashtags", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $hashtags = QueryHelper::convertApiDataToPaginate(
            items: $results['hashtags']['data']['list'],
            paginate: $results['hashtags']['data']['paginate'],
        );

        return view('profile.blocking.hashtags', compact('items', 'profile', 'hashtags'));
    }

    // blockingPosts
    public function blockingPosts(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'posts'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/block/posts", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $posts = QueryHelper::convertApiDataToPaginate(
            items: $results['posts']['data']['list'],
            paginate: $results['posts']['data']['paginate'],
        );

        return view('profile.blocking.posts', compact('items', 'profile', 'posts'));
    }

    // blockingComments
    public function blockingComments(Request $request, string $uidOrUsername)
    {
        $query = $request->all();

        $client = ApiHelper::make();

        $results = $client->unwrapRequests([
            'profile' => $client->getAsync("/api/v2/user/{$uidOrUsername}/detail"),
            'comments'   => $client->getAsync("/api/v2/user/{$uidOrUsername}/mark/block/comments", [
                'query' => $query,
            ]),
        ]);

        if ($results['profile']['code'] != 0) {
            throw new ErrorException($results['profile']['message'], $results['profile']['code']);
        }

        $items = $results['profile']['data']['items'];
        $profile = $results['profile']['data']['detail'];

        $comments = QueryHelper::convertApiDataToPaginate(
            items: $results['comments']['data']['list'],
            paginate: $results['comments']['data']['paginate'],
        );

        return view('profile.blocking.comments', compact('items', 'profile', 'comments'));
    }
}
