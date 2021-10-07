<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Base\Config;

use App\Base\Models\BaseModel;
use Illuminate\Validation\Rule;

class BaseConfig
{
    const DEFAULT_PAGE_SIZE = 30;
    const DEFAULT_LARGE_PAGE_SIZE = 500;
    const DEFAULT_ALL_IN_ONE_PAGE_SIZE = 30000;
    const DEFAULT_ROOT_ID = 1;
    const PHONE_REG = "/^1[34578]{1}\d{9}$/";
    const DEFAULT_ADMIN_ID = 1;

    const RULE_INDEX = 'index';
    const RULE_STORE = 'store';
    const RULE_UPDATE = 'update';
    const RULE_DETAIL = 'detail';
    const RULE_DESTROY = 'destroy';
    const RULE_STORE_MORE_JSON = 'store_more_json';
    const RULE_UPDATE_MORE_JSON = 'update_more_json';
    const IMPORT_RULE = [
        'excel' => ['required', 'file', 'mimes:xls,xlsx'],
    ];

    const NICKNAME = 'nickname';

    // Query Mode
    const QUERY_TYPE_DB_QUERY = 'db_query';  // Queries with join configuration support
    const QUERY_TYPE_SQL_QUERY = 'sql_query'; // Native SQL Queries

    // Database Config
    const MYSQL_CONNECTION = 'mysql';
    const MYSQL_CONNECTION_SLAVE = 'mysql_slave';
    const MYSQL_CONNECTION_HELPER = 'mysql_helper';

    // Enable
    const ENABLE_TRUE = 1;
    // const ENABLE_TRUE = true;

    // Disable
    const ENABLE_FALSE = 0;
    // const ENABLE_FALSE = false;

    const ENABLE_OPTION = [
        ['key' => self::ENABLE_TRUE, 'text' => 'Enable'],
        ['key' => self::ENABLE_FALSE, 'text' => 'Disable'],
    ];

    // Main Table: Additional query rules
    const ADDED_SEARCHABLE_FIELDS = [];

    // Append Table: Additional query rules
    const APPEND_SEARCHABLE_FIELDS = [
        'child_begin_at' => ['field' => 'start_at', 'op' => '>='],
        'child_end_at' => ['field' => 'end_at', 'op' => '<='],
    ];

    // Query rules for categorized data tables, querying only first-level nodes
    protected $treeSearchRule = [];

    // Model (default search column)
    const DEFAULT_SEARCHABLE_FIELDS = [
        'id' => ['field' => 'id', 'op' => '='],
        'ids' => ['field' => 'id', 'op' => 'IN'],
        'name' => ['field' => 'name', 'op' => 'LIKE'],
        'nickname' => ['field' => 'nickname', 'op' => 'LIKE'],
        'type' => ['field' => 'type', 'op' => 'IN'],
        'state' => ['field' => 'state', 'op' => 'IN'],
        'status' => ['field' => 'status', 'op' => 'IN'],
        'is_enable' => ['field' => 'is_enable', 'op' => '='],
        'created_at_from' => ['field' => 'created_at', 'op' => '>='],
        'created_at_to' => ['field' => 'created_at', 'op' => '<='],
        'updated_at_from' => ['field' => 'updated_at', 'op' => '>='],
        'updated_at_to' => ['field' => 'updated_at', 'op' => '<='],
    ];

    /**
     * Configuration class basic functions.
     */

    // User Type
    const USER_TYPE_ADMIN = 1; // Fresns Console Administrator
    const USER_TYPE_USER = 2; // General User

    // Gender
    const GENDER_UNKNOWN = 0;
    const GENDER_MAN = 1;
    const GENDER_FEMALE = 2;
    const GENDER_MAP = [
        self::GENDER_UNKNOWN => 'Unknown',
        self::GENDER_MAN => 'Man',
        self::GENDER_FEMALE => 'Female',
    ];

    // Login Mode
    const LOGIN_TYPE_EMAIL = 1;
    const LOGIN_TYPE_PHONE = 2;
    const LOGIN_TYPE_NAME = 3;
    const LOGIN_TYPE = [
        self::LOGIN_TYPE_EMAIL => 'email',
        self::LOGIN_TYPE_PHONE => 'phone',
        self::LOGIN_TYPE_NAME => 'login_name',
    ];

    /*
     * Corresponding table columns
     */
    public $table;

    public function __construct($table = '')
    {
        $this->table = $table;
    }

    public function getTable()
    {
        return $this->table;
    }

    // List Rules
    public function indexRule()
    {
    }

    // New Rules
    public function storeRule()
    {
    }

    // Update Rules
    public function updateRule()
    {
    }

    // Drop Rules
    public function destroyRule()
    {
        $table = $this->table;

        $rule = ['ids' => ['required',
            Rule::exists($table)->where(function ($query) {
                $query->where('deleted_at', null);
            }),
        ]];

        return $rule;
    }

    // Details Rules
    public function detailRule()
    {
        $table = $this->table;
        $rule = ['id' => ['required',
            Rule::exists($table)->where(function ($query) {
                $query->where('deleted_at', null);
            }),
        ]];

        return $rule;
    }

    // Get tree query rules
    public function getTreeSearchRule()
    {
        return $this->treeSearchRule;
    }

    // Create a new more_json rule
    public function storeMoreJsonRule()
    {
        return [];
    }

    // Update the more_json rule
    public function updateMoreJsonRule()
    {
        return [];
    }
}
