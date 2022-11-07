<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

return [
    // No login for public mode account
    'publicAccount' => [
        'api.global.configs',
        'api.global.roles',
        'api.global.maps',
        'api.global.content.type',
        'api.global.stickers',
        'api.global.block.words',
        'api.common.input.tips',
        'api.common.callback',
        'api.common.send.verifyCode',
        'api.common.upload.log',
        'api.common.file.users',
        'api.search.users',
        'api.search.groups',
        'api.search.hashtags',
        'api.search.posts',
        'api.search.comments',
        'api.account.register',
        'api.account.login',
        'api.account.reset.password',
        'api.user.list',
        'api.user.detail',
        'api.user.followers.you.follow',
        'api.user.interactive',
        'api.user.mark.list',
        'api.group.tree',
        'api.group.categories',
        'api.group.list',
        'api.group.detail',
        'api.group.interactive',
        'api.hashtag.list',
        'api.hashtag.detail',
        'api.hashtag.interactive',
        'api.post.list',
        'api.post.detail',
        'api.post.interactive',
        'api.post.user.list',
        'api.post.logs',
        'api.post.log.detail',
        'api.post.nearby',
        'api.comment.list',
        'api.comment.detail',
        'api.comment.interactive',
        'api.comment.logs',
        'api.comment.log.detail',
    ],

    // No login for private mode account
    'privateAccount' => [
        'api.global.configs',
        'api.common.callback',
        'api.common.send.verifyCode',
        'api.common.upload.log',
        'api.account.login',
        'api.account.reset.password',
    ],

    // No login for public mode user
    'publicUser' => [
        'api.global.configs',
        'api.global.roles',
        'api.global.maps',
        'api.global.content.type',
        'api.global.stickers',
        'api.global.block.words',
        'api.common.input.tips',
        'api.common.callback',
        'api.common.send.verifyCode',
        'api.common.upload.log',
        'api.common.file.users',
        'api.search.users',
        'api.search.groups',
        'api.search.hashtags',
        'api.search.posts',
        'api.search.comments',
        'api.account.register',
        'api.account.login',
        'api.account.reset.password',
        'api.account.detail',
        'api.account.wallet.logs',
        'api.account.verify.identity',
        'api.account.edit',
        'api.account.logout',
        'api.account.apply.delete',
        'api.account.revoke.delete',
        'api.user.list',
        'api.user.detail',
        'api.user.followers.you.follow',
        'api.user.mark.list',
        'api.user.auth',
        'api.group.tree',
        'api.group.categories',
        'api.group.list',
        'api.group.detail',
        'api.group.interactive',
        'api.hashtag.list',
        'api.hashtag.detail',
        'api.hashtag.interactive',
        'api.post.list',
        'api.post.detail',
        'api.post.interactive',
        'api.post.user.list',
        'api.post.logs',
        'api.post.log.detail',
        'api.post.nearby',
        'api.comment.list',
        'api.comment.detail',
        'api.comment.interactive',
        'api.comment.logs',
        'api.comment.log.detail',
    ],

    // No login for private mode user
    'privateUser' => [
        'api.global.configs',
        'api.common.callback',
        'api.common.send.verifyCode',
        'api.common.upload.log',
        'api.account.login',
        'api.account.reset.password',
        'api.account.detail',
        'api.account.wallet.logs',
        'api.account.verify.identity',
        'api.account.edit',
        'api.account.logout',
        'api.account.apply.delete',
        'api.account.revoke.delete',
        'api.user.auth',
    ],

    // Private mode inaccessible routes
    'privateRoutes' => [
        'api.common.upload.file',
        'api.common.file.link',
        'api.user.edit',
        'api.user.mark',
        'api.user.mark.note',
        'api.notify.delete',
        'api.dialog.send.message',
        'api.dialog.delete',
        'api.post.delete',
        'api.comment.delete',
        'api.editor.direct.publish',
        'api.editor.create',
        'api.editor.generate',
        'api.editor.detail',
        'api.editor.update',
        'api.editor.publish',
        'api.editor.recall',
        'api.editor.delete',
    ],
];
