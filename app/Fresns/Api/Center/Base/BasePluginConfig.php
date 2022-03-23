<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Base;

class BasePluginConfig
{
    /**
     * Plugin Global Unique Values.
     *
     * @var string
     */
    public $uniKey = '';

    /**
     * Plugin Name.
     *
     * @var string
     */
    public $name = '';

    /**
     * Plugin Type.
     *
     * @var int
     */
    public $type = 2;

    /**
     * Plugin Description.
     *
     * @var string
     */
    public $description = '';

    /**
     * Plugin Image URL.
     *
     * @var string
     */
    public $imageUrl = '';

    /**
     * Plugin Author.
     *
     * @var string
     */
    public $author = '';

    /**
     * Plugin Author Link.
     *
     * @var string
     */
    public $authorLink = '';

    /**
     * Plugin Usage Scenarios.
     *
     * @var array
     */
    public $sceneArr = [

    ];

    /**
     * The latest Semantic version number of the plugin.
     *
     * @var string
     */
    public $currVersion = '1.0.0';

    /**
     * The latest integer version number of the plugin.
     *
     * @var int
     */
    public $currVersionInt = 1;

    /**
     * Plugin directory name (upper camel case)
     * app/Plugins/$dirName
     * public/assets/$dirName
     * resources/views/plugins/$dirName
     * resources/lang/{langtag}/$dirName.
     *
     * @var string
     */
    public $dirName = '';

    /**
     * Plugin Access Path
     * Relative paths, support for variable names.
     *
     * @var string
     */
    public $accessPath = '';

    /**
     * Plugin settings path.
     *
     * @var string
     */
    public $settingPath = '';

    // Plugin default command word, any plugin must have
    public const FRESNS_CMD_DEFAULT = 'fresns_cmd_default';

    // Plugin command word callback mapping
    const FRESNS_CMD_HANDLE_MAP = [
        self::FRESNS_CMD_DEFAULT => 'defaultHandler',
    ];

    // Plugin Status Code
    const OK = 0;
    const FAIL = 1001;
    const CODE_NOT_EXIST = 1002;
    const CODE_PARAMS_ERROR = 1003;

    // Plugin status code mapping
    const CODE_MAP = [
        self::OK => 'ok',
        self::FAIL => 'fail',
        self::CODE_NOT_EXIST => 'Data does not exist',
        self::CODE_PARAMS_ERROR => 'Parameter error',
    ];
}
