<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Support\Str;

class ValidationUtility
{
    // Validate send code
    public static function sendCode(?string $account = null): bool
    {
        if (empty($account)) {
            return true;
        }

        $minuteSendCount = VerifyCode::where('account', $account)->where('created_at', '>=', now()->subMinute())->count();
        $minutesSendCount = VerifyCode::where('account', $account)->where('created_at', '>=', now()->subMinutes(10))->count();

        if ($minuteSendCount > 1 || $minutesSendCount > 5) {
            return false;
        }

        return true;
    }

    // Validate is disposable email
    public static function disposableEmail(?string $email = null): bool
    {
        if (empty($email)) {
            return true;
        }

        $url = 'https://open.kickbox.com/v1/disposable/'.Str::after($email, '@');

        try {
            return ! json_decode(file_get_contents($url), true)['disposable'];
        } catch (\Throwable $ex) {
            return true;
        }
    }

    // Validate regex patterns
    public static function regexp(string $pattern): bool
    {
        return @preg_match($pattern, 'fresns') !== false;
    }

    // Validate password
    public static function password(string $password): array
    {
        $config = ConfigHelper::fresnsConfigByItemKeys([
            'password_length',
            'password_strength',
        ]);

        $passwordLength = Str::length($password);

        $length = true;
        if ($passwordLength < $config['password_length']) {
            $length = false;
        }

        $number = true;
        if (in_array('number', $config['password_strength'])) {
            $number = preg_match('/\d/', $password);
        }

        $lowercase = true;
        if (in_array('lowercase', $config['password_strength'])) {
            $lowercase = preg_match('/[a-z]/', $password);
        }

        $uppercase = true;
        if (in_array('uppercase', $config['password_strength'])) {
            $uppercase = preg_match('/[A-Z]/', $password);
        }

        $symbols = true;
        if (in_array('symbols', $config['password_strength'])) {
            $symbols = preg_match('/[^\w\s\d]/', $password);
        }

        $validatePassword = [
            'length' => $length,
            'number' => $number,
            'lowercase' => $lowercase,
            'uppercase' => $uppercase,
            'symbols' => $symbols,
        ];

        return $validatePassword;
    }

    // Validate username
    public static function username(string $username): array
    {
        $config = ConfigHelper::fresnsConfigByItemKeys([
            'username_min',
            'username_max',
            'user_ban_names',
        ]);
        $length = Str::length($username);
        $checkUser = User::withTrashed()->where('username', $username)->first();

        // formatString
        $formatString = true;
        $isError = preg_match('/^[A-Za-z0-9-]+$/', $username);
        if (! $isError) {
            $formatString = false;
        }

        // formatHyphen
        $formatHyphen = true;
        $hyphenCount = substr_count($username, '-');
        $hyphenStrStart = str_starts_with($username, '-');
        $hyphenStrEnd = str_ends_with($username, '-');
        if ($hyphenCount > 1 || $hyphenStrStart || $hyphenStrEnd) {
            $formatHyphen = false;
        }

        // formatNumeric
        $formatNumeric = true;
        $isNumeric = is_numeric($username);
        if ($isNumeric) {
            $formatNumeric = false;
        }

        // minLength
        $minLength = true;
        if ($length < $config['username_min']) {
            $minLength = false;
        }

        // maxLength
        $maxLength = true;
        if ($length > $config['username_max']) {
            $maxLength = false;
        }

        // use
        $use = true;
        if ($checkUser) {
            $use = false;
        }

        // banName
        $banName = true;
        $newBanNames = array_map('strtolower', $config['user_ban_names']);
        $isBanName = Str::contains(Str::lower($username), $newBanNames);
        if ($isBanName) {
            $banName = false;
        }

        $validateUsername = [
            'formatString' => $formatString,
            'formatHyphen' => $formatHyphen,
            'formatNumeric' => $formatNumeric,
            'minLength' => $minLength,
            'maxLength' => $maxLength,
            'use' => $use,
            'banName' => $banName,
        ];

        return $validateUsername;
    }

    // Validate nickname
    public static function nickname(string $nickname): array
    {
        $config = ConfigHelper::fresnsConfigByItemKeys([
            'nickname_min',
            'nickname_max',
            'nickname_unique',
            'user_ban_names',
        ]);

        $length = Str::length($nickname);

        // formatString
        $formatString = true;
        $isError = preg_match('/^[\x{4e00}-\x{9fa5} A-Za-z0-9]+$/u', $nickname);
        if (! $isError) {
            $formatString = false;
        }

        // formatSpace
        $formatSpace = true;
        $spaceCount = substr_count($nickname, ' ');
        $spaceStrStart = str_starts_with($nickname, ' ');
        $spaceStrEnd = str_ends_with($nickname, ' ');
        if ($spaceCount > 1 || $spaceStrStart || $spaceStrEnd) {
            $formatSpace = false;
        }

        // minLength
        $minLength = true;
        if ($length < $config['nickname_min']) {
            $minLength = false;
        }

        // maxLength
        $maxLength = true;
        if ($length > $config['nickname_max']) {
            $maxLength = false;
        }

        // use
        $use = true;
        if ($config['nickname_unique']) {
            $checkUser = User::where('nickname', $nickname)->first();

            if ($checkUser) {
                $use = false;
            }
        }

        // banName
        $isBanName = in_array(Str::lower($nickname), $config['user_ban_names']);

        $validateNickname = [
            'formatString' => $formatString,
            'formatSpace' => $formatSpace,
            'minLength' => $minLength,
            'maxLength' => $maxLength,
            'use' => $use,
            'banName' => $isBanName ? false : true,
        ];

        return $validateNickname;
    }

    // Validate bio
    public static function bio(string $bio): array
    {
        $lengthConfig = ConfigHelper::fresnsConfigByItemKey('bio_length');

        $bioLength = Str::length($bio);

        $length = true;
        if ($bioLength > $lengthConfig) {
            $length = false;
        }

        $validateBio = [
            'length' => $length,
        ];

        return $validateBio;
    }

    // validation user mark
    public static function userMarkOwn(int $userId, int|string $markType, int $markId): bool
    {
        if (is_string($markType)) {
            $markType = match ($markType) {
                'user' => InteractionUtility::TYPE_USER,
                'group' => InteractionUtility::TYPE_GROUP,
                'hashtag' => InteractionUtility::TYPE_HASHTAG,
                'geotag' => InteractionUtility::TYPE_GEOTAG,
                'post' => InteractionUtility::TYPE_POST,
                'comment' => InteractionUtility::TYPE_COMMENT,
                default => null,
            };
        }

        if ($markType == InteractionUtility::TYPE_USER && $userId == $markId) {
            return false;
        }

        if ($markType == InteractionUtility::TYPE_POST || $markType == InteractionUtility::TYPE_COMMENT) {
            switch ($markType) {
                case InteractionUtility::TYPE_POST:
                    $authorId = Post::where('id', $markId)->value('user_id');
                    break;

                case InteractionUtility::TYPE_COMMENT:
                    $authorId = Comment::where('id', $markId)->value('user_id');
                    break;
            }

            if ($authorId == $userId) {
                return false;
            }
        }

        return true;
    }

    // Validate draft
    public static function draft(string $type, array $draft): int
    {
        // $draft = [
        //     'userId' => null,
        //     'postId' => null,
        //     'postGroupId' => null,
        //     'postTitle' => null,
        //     'commentId' => null,
        //     'commentPostId' => null,
        //     'content' => null,
        // ];

        $editorConfig = ConfigHelper::fresnsConfigByItemKeys([
            'post_editor_group',
            'post_editor_group_required',
            'post_editor_title',
            'post_editor_title_required',
            'post_editor_title_length',
            "{$type}_editor_content_length",
            "{$type}_edit_time_limit",
        ]);

        if (empty($draft['content'])) {
            return 38204;
        }

        $contentLength = Str::length($draft['content']);
        if ($contentLength > $editorConfig["{$type}_editor_content_length"]) {
            return 38205;
        }

        switch ($type) {
            case 'post':
                if ($draft['postId']) {
                    $post = PrimaryHelper::fresnsModelById('post', $draft['postId']);

                    if ($post?->created_at) {
                        $checkContentEditPerm = PermissionUtility::checkContentEditPerm($post->created_at, $editorConfig['post_edit_time_limit']);

                        if (! $checkContentEditPerm['editableStatus']) {
                            return 36309;
                        }
                    }
                }

                if ($editorConfig['post_editor_group'] && $editorConfig['post_editor_group_required'] && empty($draft['postGroupId'])) {
                    return 38208;
                }

                if ($editorConfig['post_editor_title'] && $editorConfig['post_editor_title_required'] && empty($draft['postTitle'])) {
                    return 38202;
                }

                if ($draft['postTitle']) {
                    $titleLength = Str::length($draft['postTitle']);
                    if ($titleLength > $editorConfig['post_editor_title_length']) {
                        return 38203;
                    }
                }

                if ($draft['postGroupId']) {
                    $group = PrimaryHelper::fresnsModelById('group', $draft['postGroupId']);

                    if (! $group) {
                        return 37100;
                    }

                    if (! $group->is_enabled) {
                        return 37101;
                    }

                    $checkGroup = PermissionUtility::checkUserGroupPublishPerm($draft['postGroupId'], $group->permissions, $draft['userId']);

                    if (! $checkGroup['canPublish']) {
                        return 36313;
                    }

                    if (! $checkGroup['allowPost']) {
                        return 36311;
                    }

                    // Review
                    if ($checkGroup['reviewPost']) {
                        return 38200;
                    }
                }
                break;

            case 'comment':
                if ($draft['commentId']) {
                    $comment = PrimaryHelper::fresnsModelById('comment', $draft['commentId']);

                    if ($comment?->created_at) {
                        $checkContentEditPerm = PermissionUtility::checkContentEditPerm($comment->created_at, $editorConfig['comment_edit_time_limit']);

                        if (! $checkContentEditPerm['editableStatus']) {
                            return 36309;
                        }
                    }
                }

                $checkCommentPerm = PermissionUtility::checkPostCommentPerm($draft['commentPostId'], $draft['userId']);
                if (! $checkCommentPerm['status']) {
                    return $checkCommentPerm['code'];
                }

                $post = PrimaryHelper::fresnsModelById('post', $draft['commentPostId']);
                if ($post?->group_id) {
                    $group = PrimaryHelper::fresnsModelById('group', $post->group_id);

                    if (! $group) {
                        return 37100;
                    }

                    if (! $group->is_enabled) {
                        return 37101;
                    }

                    $checkGroup = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $draft['userId']);

                    if (! $checkGroup['allowComment']) {
                        return 36312;
                    }

                    // Review
                    if ($checkGroup['reviewComment']) {
                        return 38200;
                    }
                }
                break;
        }

        // Publish config
        $publishConfig = ConfigUtility::getPublishConfigByType($type, $draft['userId']);

        // check perm
        $permConfig = $publishConfig['perm'];
        if ($permConfig['review']) {
            return 38200;
        }

        // check limit
        $limitConfig = $publishConfig['limit'];
        $checkRule = true;
        if ($limitConfig['status'] && $limitConfig['isInTime'] && $limitConfig['rule'] == 1) {
            $checkRule = false;
        }

        if (! $checkRule) {
            return 38200;
        }

        return 0;
    }
}
