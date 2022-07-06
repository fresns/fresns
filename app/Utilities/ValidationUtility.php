<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\BlockWord;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Support\Facades\Cache;
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
    public static function disposableEmail(string $email): bool
    {
        $url = 'https://open.kickbox.com/v1/disposable/'.Str::after($email, '@');

        try {
            return ! json_decode(file_get_contents($url), true)['disposable'];
        } catch (\Throwable $ex) {
            return true;
        }
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
            $number = preg_match('/\d/is', $password);
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
            $symbols = preg_match('/^[A-Za-z0-9]+$/', $password);
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
            'ban_names',
        ]);
        $length = Str::length($username);
        $user = User::withTrashed()->where('username', $username)->first();

        $formatString = true;
        $isError = preg_match('/^[A-Za-z0-9-]+$/', $username);
        if (! $isError) {
            $formatString = false;
        }

        $formatHyphen = true;
        $hyphenCount = substr_count($username, '-');
        $hyphenStrStart = str_starts_with($username, '-');
        $hyphenStrEnd = str_ends_with($username, '-');
        if ($hyphenCount > 1 || $hyphenStrStart || $hyphenStrEnd) {
            $formatHyphen = false;
        }

        $formatNumeric = true;
        $isNumeric = is_numeric($username);
        if ($isNumeric) {
            $formatNumeric = false;
        }

        $minLength = true;
        if ($length < $config['username_min']) {
            $minLength = false;
        }

        $maxLength = true;
        if ($length > $config['username_max']) {
            $maxLength = false;
        }

        $use = true;
        if (! empty($user)) {
            $use = false;
        }

        $banName = true;
        $newBanNames = array_map('strtolower', $config['ban_names']);
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
        ]);
        $length = Str::length($nickname);

        $formatString = true;
        $isError = preg_match('/^[\x{4e00}-\x{9fa5} A-Za-z0-9]+$/u', $nickname);
        if (! $isError) {
            $formatString = false;
        }

        $formatSpace = true;
        $spaceCount = substr_count($nickname, ' ');
        $spaceStrStart = str_starts_with($nickname, ' ');
        $spaceStrEnd = str_ends_with($nickname, ' ');
        if ($spaceCount > 1 || $spaceStrStart || $spaceStrEnd) {
            $formatSpace = false;
        }

        $minLength = true;
        if ($length < $config['username_min']) {
            $minLength = false;
        }

        $maxLength = true;
        if ($length > $config['username_max']) {
            $maxLength = false;
        }

        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();
        $banNames = Cache::remember('fresns_user_ban_words', $cacheTime, function () {
            $banNames = BlockWord::where('user_mode', 3)->pluck('word')->toArray();

            return array_map('strtolower', $banNames);
        });

        $banName = true;
        $isBanName = Str::contains(Str::lower($nickname), $banNames);
        if ($isBanName) {
            $banName = false;
        }

        $validateNickname = [
            'formatString' => $formatString,
            'formatSpace' => $formatSpace,
            'minLength' => $minLength,
            'maxLength' => $maxLength,
            'banName' => $banName,
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

        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();
        $banNames = Cache::remember('fresns_user_ban_words', $cacheTime, function () {
            $banNames = BlockWord::where('user_mode', 3)->pluck('word')->toArray();

            return array_map('strtolower', $banNames);
        });

        $banWord = true;
        $isBanWord = Str::contains(Str::lower($bio), $banNames);
        if ($isBanWord) {
            $banWord = false;
        }

        $validateBio = [
            'length' => $length,
            'banWord' => $banWord,
        ];

        return $validateBio;
    }

    // validation user mark
    public static function userMarkOwn(int $userId, int $markType, int $markId): bool
    {
        if (! is_numeric($markType)) {
            $markType = match ($markType) {
                default => null,
                'user' => 1,
                'group' => 2,
                'hashtag' => 3,
                'post' => 4,
                'comment' => 5,
            };
        }

        if ($markType == InteractiveUtility::TYPE_USER && $userId == $markId) {
            return false;
        }

        if ($markType == InteractiveUtility::TYPE_POST || $markType == InteractiveUtility::TYPE_COMMENT) {
            switch ($markType) {
                case InteractiveUtility::TYPE_POST:
                    $creator = Post::where('id', $markId)->value('user_id');
                break;

                case InteractiveUtility::TYPE_COMMENT:
                    $creator = Comment::where('id', $markId)->value('user_id');
                break;
            }

            if ($creator == $userId) {
                return false;
            }
        }

        return true;
    }

    // Validate content ban words
    public static function contentBanWords(string $content): bool
    {
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $lowerBanWords = Cache::remember('fresns_content_ban_words', $cacheTime, function () {
            $banWords = BlockWord::where('content_mode', 3)->pluck('word')->toArray();

            return array_map('strtolower', $banWords);
        });

        return ! Str::contains(Str::lower($content), $lowerBanWords);
    }

    // Validate content is review
    public static function contentReviewWords(string $content): bool
    {
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $lowerReviewWords = Cache::remember('fresns_content_review_words', $cacheTime, function () {
            $reviewWords = BlockWord::where('content_mode', 4)->pluck('word')->toArray();

            return array_map('strtolower', $reviewWords);
        });

        return ! Str::contains(Str::lower($content), $lowerReviewWords);
    }

    // Validate message ban words
    public static function messageBanWords(string $message): bool
    {
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $lowerBanWords = Cache::remember('fresns_dialog_ban_words', $cacheTime, function () {
            $banWords = BlockWord::where('dialog_mode', 3)->pluck('word')->toArray();

            return array_map('strtolower', $banWords);
        });

        return ! Str::contains(Str::lower($message), $lowerBanWords);
    }
}
