<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Base\Models;

use App\Base\Config\BaseConfig;
use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BaseQuery
{
    // Query Columns
    protected $searchableFields;

    protected $joinSearchableFields;

    // Sort Columns
    protected $orderByFields;

    // Model
    protected $model;
    protected $table;
    protected $query;

    // Pagination
    protected $pageInfo;
    protected $page;
    protected $limit;
    protected $queryTotalCount;

    public function __construct(BaseModel $model, $opts = [])
    {
        $this->model = $model;
        $this->query = $this->model;
        $this->table = $this->model->getTable();
        $this->page = 1;
        $this->limit = $this->getLimit();
        $this->searchableFields = $this->model->initSearchableFields();
        $this->orderByFields = $this->model->initOrderByFields();
        $this->dbQuery = DB::table($this->table);
        $this->joinSearchableFields = $this->model->getJoinSearchableFields();
    }

    // Initialize search cond
    public function initWhereCond()
    {
        $req = request();
        foreach ($this->searchableFields as $searchField => $arr) {
            // Skip (No key)
            if (! $req->has($searchField)) {
                continue;
            }

            $searchValue = $req->input($searchField);
            // Skip (No value)
            if ($searchValue === null) {
                continue;
            }

            // Validate array fields
            ValidateService::validParamExist($arr, ['field', 'op']);

            $field = $arr['field'];
            $op = $arr['op'];

            $upperOp = strtoupper($op);

            switch ($upperOp) {
                case '=':
                    $this->query = $this->query->where($field, $searchValue);
                    break;
                case 'IN':
                    $inArr = explode(',', $searchValue);
                    $this->query = $this->query->whereIn($field, $inArr);
                    break;
                case '>=':
                    $this->query = $this->query->where($field, '>=', $searchValue);
                    break;
                case '<=':
                    $this->query = $this->query->where($field, '<=', $searchValue);
                    break;
                case '<>':
                    $this->query = $this->query->where($field, '!=', $searchValue);
                    break;
                case 'LIKE':
                    $this->query = $this->query->where($field, 'LIKE', '%'.$searchValue.'%');
                    break;
                case 'DATE_REGION_BEGIN_AT':
                    $this->query = $this->query->where($field, '>=', $searchValue);
                    break;
                case 'DATE_REGION_END_AT':
                    $this->query = $this->query->where($field, '<=', $searchValue);
                    break;
                case 'JSON_ITEM_ARR':
                    $inArr = explode(',', $searchValue);
                    $fieldAttr = $arr['field_attr'] ?? $field;
                    foreach ($inArr as $idx => $value) {
                        if (empty($value)) {
                            continue;
                        }
                        $value = trim($value);
                        $this->query = $this->query->whereJsonContains($field, [$fieldAttr => intval($value)]);
                        if ($idx > 0) {
                            $this->query = $this->query->whereJsonContains($field, [$fieldAttr => intval($value)], 'OR');
                        }
                    }
                    break;
                case'JSON_NUMBER':
                    $inArr = explode(',', $searchValue);
                    foreach ($inArr as &$value) {
                        $value = intval($value);
                    }
                    // App\User::where('meta->skills', 'like', '%Vue%')->get()
                    $this->query = $this->query->whereJsonContains($field, $inArr);

                    break;
                case 'JOIN':
                    $inArr = explode(',', $field); // Special handle to achieve multi-table join query
                    $cnt = 0; $resArr = [];
                    foreach ($inArr as $value) {
                        $resArr[$cnt] = $value;
                        $cnt++;
                    }
                    $this->query = $this->query->join($resArr[0], $resArr[1], $resArr[2], $resArr[3]);
                    break;
            }
        }
    }

    // Initial sorting
    public function initOrderBy()
    {
        foreach ($this->orderByFields as $orderByField => $orderType) {
            $this->query = $this->query->orderBy($orderByField, $orderType);
        }
    }

    // Execution of queries
    public function executeQuery()
    {
        $req = request();
        $this->page = $req->input('currentPage', 1);
        $this->limit = $req->input('pageSize', $this->getLimit());

        // Initialize query parameters
        $this->initWhereCond();
        $this->initOrderBy();
        $req->offsetSet('page', $this->page);

        $result = $this->query->paginate($this->getLimit(), $this->table.'.*');

        $pagination = $this->setSearchPageInfo($result);

        $ret['result'] = $result;
        $ret['pagination'] = $pagination;

        return $ret;
    }

    // Execute the query for all the data that match the criteria
    public function executeQueryAll()
    {
        $req = request();

        // Initialize query parameters
        $this->initWhereCond();
        $this->initOrderBy();
        $req->offsetSet('page', $this->page);

        $result = $this->query->get();

        return $result;
    }

    // Execute a query for data that matches the conditions and returns only a single field
    public function executeQueryField($field)
    {
        $req = request();

        // Initialize query parameters
        $this->initWhereCond();
        $this->initOrderBy();
        $req->offsetSet('page', $this->page);

        $result = $this->query->pluck($this->table.'.'.$field);

        return $result;
    }

    public function getLimit()
    {
        $req = request();
        $limit = intval($req->input('pageSize', $this->model->pageSize));

        return intval($limit);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setSearchPageInfo(LengthAwarePaginator $searchRes)
    {
        $pageInfo = [];
        $pageInfo['total'] = $searchRes->total();    // Total
        $pageInfo['current'] = $searchRes->currentPage();    // Current page
        $pageInfo['pageSize'] = $searchRes->perPage();    // Number per page
        $pageInfo['lastPage'] = $searchRes->lastPage();    // Last Page

        if ($searchRes->perPage() == BaseConfig::DEFAULT_LARGE_PAGE_SIZE) {
            $perPage = BaseConfig::DEFAULT_LARGE_PAGE_SIZE.'';
            $pageInfo['pageSizeOptions'] = [$perPage];
            $pageInfo['hideOnSinglePage'] = true;
        }
        $this->pageInfo = $pageInfo;

        return $pageInfo;
    }

    // Initialize DB search criteria
    public function initWhereCondForDbQuery()
    {
        $req = request();
        $mainTable = $this->table;
        $selectFields = [
            "{$mainTable}.*",
        ];

        DB::connection()->enableQueryLog();  // Turn on Query Log

        // Initialize master table cond
        $this->buildDbQueryWithSearchCondArr($mainTable, $this->searchableFields);

        // Default cond
        if ($this->model->hasDeletedAt) {
            $this->dbQuery->where($mainTable.'.deleted_at', '=', null);
        }

        // Set join cond
        foreach ($this->joinSearchableFields as $joinItem) {
            // join Table
            $joinTable = $joinItem['join_table'];
            // join Table select Column
            $joinSelectFields = $joinItem['join_select_fields'];
            $selectFields = array_merge($selectFields, $joinSelectFields);
            // join cond
            $joinCondArr = $joinItem['join_cond_arr'];

            // join Table query cond
            $joinTableCondArr = $joinItem['join_table_cond_arr'];

            $needJoin = $this->buildDbQueryWithSearchCondArr($joinTable, $joinTableCondArr);

            if (! $needJoin) {
                continue;
            }

            $this->dbQuery->join($joinTable, function ($join) use ($mainTable, $joinTable, $joinCondArr) {
                foreach ($joinCondArr as $joinCond) {
                    $mainTableField = $joinCond['main_table_field'];
                    $joinTableField = $joinCond['join_table_field'];
                    $op = $joinCond['op'];
                    $join->on("{$mainTable}.{$mainTableField}", $op, "{$joinTable}.{$joinTableField}");
                }
            });
        }
    }

    // Generate cond for the table
    public function buildDbQueryWithSearchCondArr($table, $searchCondArr)
    {
        // Data Table Prefix
        $dbPrefix = env('DB_PREFIX');
        LogService::info("DB PREFIX [$dbPrefix]");
        $table = Str::startsWith($dbPrefix, $table) ? $table : $dbPrefix.$table;

        $needJoinArr = [];
        $req = request();
        foreach ($searchCondArr as $searchField => $arr) {
            // Skip (No key)
            if (! $req->has($searchField)) {
                continue;
            }

            $searchValue = $req->input($searchField);

            // Skip (No value)
            if ($searchValue === null) {
                continue;
            }

            $needJoinArr[] = $searchValue;

            // Validate array fields
            ValidateService::validParamExist($arr, ['field', 'op']);

            // Attached table
            $field = $table.'.'.$arr['field'];
            $op = $arr['op'];

            $upperOp = strtoupper($op);

            switch ($upperOp) {
                case '=':
                    $this->dbQuery = $this->dbQuery->where($field, $searchValue);
                    break;
                case 'IN':
                    $inArr = explode(',', $searchValue);
                    $this->dbQuery = $this->dbQuery->whereIn($field, $inArr);
                    break;
                case '>=':
                    $this->dbQuery = $this->dbQuery->where($field, '>=', $searchValue);
                    break;
                case '<=':
                    $this->dbQuery = $this->dbQuery->where($field, '<=', $searchValue);
                    break;
                case 'LIKE':
                    $this->dbQuery = $this->dbQuery->whereRaw($field.' LIKE ?', ['%'.$searchValue.'%']);
                    break;
                case 'DATE_REGION_BEGIN_AT':
                    $this->dbQuery = $this->dbQuery->where($field, '>=', $searchValue);
                    break;
                case 'DATE_REGION_END_AT':
                    $this->dbQuery = $this->dbQuery->where($field, '<=', $searchValue);
                    break;
            }
        }

        // Greater than 0 to require join
        return count($needJoinArr) > 0;
    }

    // Execution of queries
    public function executeDbQuery()
    {
        $req = request();
        $this->page = $req->input('currentPage', 1);
        $this->limit = $req->input('pageSize', $this->getLimit());

        // Initialize query parameters
        $this->initWhereCondForDbQuery();
        $this->initOrderBy();
        $req->offsetSet('page', $this->page);

        $result = $this->dbQuery->paginate($this->getLimit());

        $pagination = $this->setSearchPageInfo($result);

        $ret['result'] = $result;
        $ret['pagination'] = $pagination;

        return $ret;
    }

    public function executeSqlQuery()
    {
        $req = request();
        $this->page = $req->input('currentPage', 1);
        $this->limit = $req->input('pageSize', $this->getLimit());

        $req->offsetSet('page', $this->page);

        DB::connection()->enableQueryLog();  // Turn on Query Log

        $sqlQuery = $this->model->getRawSqlQuery();

        $result = $sqlQuery->paginate($this->getLimit());
        $pagination = $this->setSearchPageInfo($result);

        $queries = \DB::getQueryLog();

        LogService::info('Execution Statements: ', $queries);

        $ret['result'] = $result;
        $ret['pagination'] = $pagination;

        return $ret;
    }
}
