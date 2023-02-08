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
        if (! empty($user)) {
            $use = false;
        }

        // banName
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
            'ban_names',
        ]);
        $length = Str::length($nickname);

        $cacheKey = 'fresns_user_ban_words';
        $cacheTag = 'fresnsConfigs';

        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            $banNames = [];
        } else {
            $banNames = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($banNames)) {
                $banNameData = BlockWord::where('user_mode', 3)->pluck('word')->toArray();

                $banNames = array_map('strtolower', $banNameData);

                CacheHelper::put($banNames, $cacheKey, $cacheTag);
            }
        }

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

        // banName
        $configBanNames = array_map('strtolower', $config['ban_names']);
        $newBanNames = array_merge($banNames, $configBanNames);
        $isBanName = Str::contains(Str::lower($nickname), $newBanNames);

        $validateNickname = [
            'formatString' => $formatString,
            'formatSpace' => $formatSpace,
            'minLength' => $minLength,
            'maxLength' => $maxLength,
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

        $cacheKey = 'fresns_user_ban_words';
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            $banNameArray = [];
            $isBanWord = false;
        } else {
            $banNameArray = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($banNameArray)) {
                $banNameData = BlockWord::where('user_mode', 3)->pluck('word')->toArray();

                $banNameArray = array_map('strtolower', $banNameData);

                CacheHelper::put($banNameArray, $cacheKey, $cacheTag);
            }

            $isBanWord = Str::contains(Str::lower($bio), $banNameArray);
        }

        $validateBio = [
            'length' => $length,
            'banWord' => $isBanWord ? false : true,
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

        if ($markType == InteractionUtility::TYPE_USER && $userId == $markId) {
            return false;
        }

        if ($markType == InteractionUtility::TYPE_POST || $markType == InteractionUtility::TYPE_COMMENT) {
            switch ($markType) {
                case InteractionUtility::TYPE_POST:
                    $creator = Post::where('id', $markId)->value('user_id');
                break;

                case InteractionUtility::TYPE_COMMENT:
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
        $cacheKey = 'fresns_content_ban_words';
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return true;
        }

        $lowerBanWords = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($lowerBanWords)) {
            $banWords = BlockWord::where('content_mode', 3)->pluck('word')->toArray();

            $lowerBanWords = array_map('strtolower', $banWords);

            CacheHelper::put($lowerBanWords, $cacheKey, $cacheTag);
        }

        return ! Str::contains(Str::lower($content), $lowerBanWords);
    }

    // Validate content is review
    public static function contentReviewWords(string $content): bool
    {
        $cacheKey = 'fresns_content_review_words';
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return true;
        }

        $lowerReviewWords = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($lowerReviewWords)) {
            $reviewWords = BlockWord::where('content_mode', 4)->pluck('word')->toArray();

            $lowerReviewWords = array_map('strtolower', $reviewWords);

            CacheHelper::put($lowerReviewWords, $cacheKey, $cacheTag);
        }

        return ! Str::contains(Str::lower($content), $lowerReviewWords);
    }

    // Validate message ban words
    public static function messageBanWords(string $message): bool
    {
        $cacheKey = 'fresns_conversation_ban_words';
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return true;
        }

        $lowerBanWords = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($lowerBanWords)) {
            $banWords = BlockWord::where('conversation_mode', 3)->pluck('word')->toArray();

            $lowerBanWords = array_map('strtolower', $banWords);

            CacheHelper::put($lowerBanWords, $cacheKey, $cacheTag);
        }

        return ! Str::contains(Str::lower($message), $lowerBanWords);
    }
}
