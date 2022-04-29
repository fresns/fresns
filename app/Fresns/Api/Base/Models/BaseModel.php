<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Base\Models;

use App\Fresns\Api\Base\Config\BaseConfig;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsCmd\FresnsSubPlugin;
use App\Fresns\Api\FsCmd\FresnsSubPluginConfig;
use App\Fresns\Api\Traits\HookModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    use SoftDeletes;
    use HookModelTrait;

    // My Database Connection
    protected $myConnection = BaseConfig::MYSQL_CONNECTION;

    protected $dates = ['deleted_at'];

    public $useCache = false;
    public $pageSize = BaseConfig::DEFAULT_PAGE_SIZE;
    protected $config = null;
    public $hasDeletedAt = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->initConnection();
        $this->hookModelInit();
    }

    // Setting connection
    protected function initConnection()
    {
        DB::setDefaultConnection($this->myConnection);
    }

    // Searchable columns
    public function initSearchableFields()
    {
        $searchableFields = BaseConfig::DEFAULT_SEARCHABLE_FIELDS;
        $searchableFields = array_merge($searchableFields, $this->getAddedSearchableFields());
        $searchableFields = array_merge($searchableFields, $this->getAppendSearchableFields());

        return $searchableFields;
    }

    // Get the added search columns
    public function getAddedSearchableFields()
    {
        return [];
    }

    // Get the search columns of the append table
    public function getAppendSearchableFields()
    {
        return [];
    }

    // Get the search columns of the join table (used only when dbQuery)
    public function getJoinSearchableFields()
    {
        return [];
    }

    // Search for sorted columns
    public function initOrderByFields()
    {
        $orderByFields = [
            // 'rank_num' => 'ASC',
            'id' => 'DESC',
            // 'updated_at' => 'DESC',
        ];

        return $orderByFields;
    }

    // New
    public function store($input)
    {
        $id = DB::table($this->table)->insertGetId($input);
        // Operation after adding
        $this->hookStoreAfter($id);
        // Call the plugin to subscribe to the command word
        // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ADD_TABLE;
        // $input = [
        //     'tableName' => $this->table,
        //     'insertId' => $id,
        // ];
        // LogService::info('table_input', $input);
        // CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);

        return $id;
    }

    // Batch Add
    public function batchStore($inputArr)
    {
        $rs = DB::table($this->table)->insert($inputArr);

        return $rs;
    }

    // Update if data exists, add if it doesn't
    public function updateOrInsertByCond($cond, $input = [])
    {
        return self::updateOrInsert($cond, $input);
    }

    // Update
    public function updateItem($id, $upInput)
    {
        self::where('id', $id)->update($upInput);
        // $this->hookUpdateAfter($id);
    }

    // Updated operations. For example: updating schedules, calculating properties, etc.
    public function updateItemAfter($id)
    {
        if (in_array('hookUpdateAfter', get_class_methods($this))) {
            $this->hookUpdateAfter($id);
        }
    }

    // Update if it already exists, create if it doesn't
    public static function updateOrCreateItem($cond, $input)
    {
        $c = get_called_class();
        $m = new $c;
        $m->updateOrCreate($cond, $input);

        return true;
    }

    // Batch delete
    public function destroyByIdArr($idArr)
    {
        if ($this->canDelete($idArr)) {
            $this->hookDestroyBefore($idArr);
            self::whereIn('id', $idArr)->delete();
            // $this->updateDestroyAccountId($idArr);
        }
    }

    // Recovery (Logical Deletion)
    public static function restoreItem($id)
    {
        self::withTrashed()->find($id)->restore();
    }

    // Recover multiple (Logical Deletion)
    public static function batchRestore($cond)
    {
        self::withTrashed()->where($cond)->restore();
    }

    // Physical Deletion
    public static function forceDeleteItem($id)
    {
        self::withTrashed()->find($id)->forceDelete();
    }

    // Query a single
    public static function find($id)
    {
        $c = get_called_class();
        $m = new $c;

        return $m->findById($id);
    }

    // Query Find By Field
    public static function staticFindByField($field, $value)
    {
        $c = get_called_class();
        $m = new $c;

        return $m->findByField($field, $value);
    }

    // Query append table
    public static function findAppend($field, $value)
    {
        $c = get_called_class();
        $m = new $c;

        return $m->findByField($field, $value);
    }

    // Query append table (Search by condition)
    public static function findAppendByCond($cond)
    {
        $c = get_called_class();
        $m = new $c;

        return $m->findByCond($cond);
    }

    // Query a single
    public function findById($id)
    {
        return self::where('id', $id)->first();
    }

    // Query a single (Column Cond)
    public function findByField($fieldName, $fieldValue)
    {
        return self::where($fieldName, $fieldValue)->first();
    }

    // Query a single (Cond)
    public function findByCond($cond)
    {
        return self::where($cond)->first();
    }

    // Query a single (Nickname Cond)
    public static function staticFindByNickname($nickname)
    {
        return self::where('nickname', $nickname)->first();
    }

    // Query column value (e.g. get name based on id)
    public static function findValueById($id, $field = 'name')
    {
        return self::where('id', $id)->value($field);
    }

    // Query column value (e.g. Get an array of tag names based on tag idArr)
    public static function getValueArrByIdArr($idArr, $field = 'name')
    {
        return self::whereIn('id', $idArr)->pluck($field);
    }

    // Query by where condition and return an array
    public static function getValueArrByCond($cond, $field = 'name')
    {
        return self::where($cond)->pluck($field)->toArray();
    }

    // Query by whereIn condition and return the array
    public static function getValueArrByCondIn($key = 'id', $valueArr = [], $field = 'name')
    {
        return self::whereIn($key, $valueArr)->pluck($field)->toArray();
    }

    // Query a single
    public function getByCond($cond = [])
    {
        return self::where($cond)->get();
    }

    // Query multiple
    public static function getByStaticWithCond($cond = [], $column = ['*'])
    {
        $c = get_called_class();
        $m = new $c;

        return self::where($cond)->get($column);
    }

    // Query (rank_num)
    public static function getByStaticWithCondArr($cond = [], $column = ['*'])
    {
        $c = get_called_class();
        $m = new $c;

        return self::where($cond)->orderBy('rank_num')->get($column)->toArray();
    }

    // Query multiple (Specify column)
    public function getByCondWithFields($cond, $fields)
    {
        return self::where($cond)->select($fields)->get();
    }

    // Map: staticGetByCondKVMap
    public static function staticGetByCondKVMap($k = 'id', $v = 'name', $cond = [])
    {
        $c = get_called_class();
        $m = new $c;

        return $m->getByCondKVMap($k, $v, $cond);
    }

    // Map: Combine columns according to conditions
    public function getByCondKVMap($k = 'id', $v = 'name', $cond = [])
    {
        return DB::table($this->table)->where($cond)->whereNull('deleted_at')->pluck($v, $k);
    }

    // Map: Multi-Criteria Search
    public static function getMultipleCond($map, $column = ['*'])
    {
        $query = self::query();
        foreach ($map as $dataArr) {
            $condition = key($dataArr);
            $field = key($dataArr[$condition]);
            $value = current($dataArr[$condition]);

            switch (strtoupper($condition)) {
                case '=':
                    $query = $query->where($field, $value);
                    break;
                case 'LIKE':
                    $query = $query->whereRaw($field.' LIKE ?', ['%'.$value.'%']);
                    break;
                case 'IN':
                    if (! is_array($value)) {
                        $value = explode(',', $value);
                    }
                    $query = $query->whereIn($field, $value);
                    break;
                case '>=':
                    $query = $query->where($field, '>=', $value);
                    break;
                case '<=':
                    $query = $query->where($field, '<=', $value);
                    break;
            }
        }

        return $query->get($column);
    }

    // Multi-criteria search, paginated data
    public static function getMultipleCondPage($map, $page_param, $column = ['*'])
    {
        $query = self::query();
        foreach ($map as $dataArr) {
            $condition = key($dataArr);
            $field = key($dataArr[$condition]);
            $value = current($dataArr[$condition]);

            switch (strtoupper($condition)) {
                case '=':
                    $query = $query->where($field, $value);
                    break;
                case 'LIKE':
                    $query = $query->whereRaw($field.' LIKE ?', ['%'.$value.'%']);
                    break;
                case 'IN':
                    if (! is_array($value)) {
                        $value = explode(',', $value);
                    }
                    $query = $query->whereIn($field, $value);
                    break;
                case '>=':
                    $query = $query->where($field, '>=', $value);
                    break;
                case '<=':
                    $query = $query->where($field, '<=', $value);
                    break;
                case '!=':
                    $query = $query->where($field, '!=', $value);
                    break;
            }
        }
        request()->offsetSet('page', $page_param['page']);

        $query->orderByDesc('id');
        $result = $query->paginate($page_param['limit'])->toArray();

        $pageInfo['total'] = $result['total'];
        $pageInfo['current'] = $result['current_page'];
        $pageInfo['pageSize'] = $result['per_page'];

        $data = [
            'list' => $result['data'],
            'pagination' => $pageInfo,
        ];

        return $data;
    }

    // Batch Update
    public function updateBatch($multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception('Data cannot be empty');
            }

            $tableName = DB::getTablePrefix().$this->getTable(); // Table Name
            $firstRow = current($multipleData);
            $updateColumn = array_keys($firstRow);

            // Update by id by default, or the first column if there is no ID
            $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
            unset($updateColumn[0]);

            // Splicing sql statements
            $updateSql = 'UPDATE '.$tableName.' SET ';
            $sets = [];
            $bindings = [];

            foreach ($updateColumn as $uColumn) {
                $setSql = '`'.$uColumn.'` = CASE ';
                foreach ($multipleData as $data) {
                    $setSql .= 'WHEN `'.$referenceColumn.'` = ? THEN ? ';
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= 'ELSE `'.$uColumn.'` END ';
                $sets[] = $setSql;
            }

            $updateSql .= implode(', ', $sets);
            $whereIn = collect($multipleData)->pluck($referenceColumn)->values()->all();
            $bindings = array_merge($bindings, $whereIn);
            $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
            $updateSql = rtrim($updateSql, ', ').' WHERE `'.$referenceColumn.'` IN ('.$whereIn.')';

            // Pass in pre-processed sql statements and corresponding bound data
            $ret = DB::update($updateSql, $bindings);

            return $ret;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Static methods
    public static function staticBuildSelectOptions($key = 'id', $text = 'name', $cond = [], $price = 'price')
    {
        $c = get_called_class();
        $m = new $c;

        return $m->buildSelectOptions($key, $text, $cond, $price);
    }

    // Component drop-down box selection
    public function buildSelectOptions($key = 'id', $text = 'name', $cond = [], $price = 'price')
    {
        if (Schema::hasColumn('accounts', 'rank_num')) {
            $items = self::where($cond)->orderBy('rank_num', 'ASC')->get();
        } else {
            $items = self::where($cond)->get();
        }
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['key'] = $item->$key;
            $it['text'] = $item->$text;
            if (! empty($item->login_name)) {
                $it['text'] .= '['.$item->login_name.']';
            }
            // $it['login_name'] = $item->login_name;
            // $it['name'] = $item->name;
            if ($price == 'show_price') {
                $it['price'] = $item->price_sale;
            }
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    // Static methods
    public static function staticBuildSelectOptions2($key = 'id', $text = 'name', $cond = [], $price = 'price')
    {
        $c = get_called_class();
        $m = new $c;

        return $m->buildSelectOptions2($key, $text, $cond, $price);
    }

    public function buildSelectOptions2($key = 'id', $text = 'name', $cond = [])
    {
        $items = self::where($cond)->get();
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['key'] = $item->$key;
            $it['text'] = $item->$text;
            if (! empty($item->login_name)) {
                $it['text'] .= '['.$item->login_name.']';
            }
            // $it['login_name'] = $item->login_name;
            // $it['name'] = $item->name;
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    // Assemble drop-down box array query
    public static function getBuildSelectOptions($key = 'id', $text = 'name', $fieldValue = [], $fieldName = 'id')
    {
        $items = self::whereIn($fieldName, $fieldValue)->orderBy('rank_num', 'ASC')->get();
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['key'] = $item->$key;
            $it['text'] = $item->$text;
            // $it['name'] = $item->name;
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    public static function buildSelectTreeData($key = 'id', $text = 'name', $cond = [])
    {
        $items = self::where($cond)->orderBy('rank_num', 'ASC')->get();
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['key'] = $item->$key;
            $it['value'] = $item->$key;
            $it['name'] = $item->$text;
            $it['title'] = $item->$text;
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    public static function buildSelectTreeDataByNoRankNum($key = 'id', $text = 'name', $cond = [])
    {
        $items = self::where($cond)->get();
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['key'] = $item->$key;
            $it['value'] = $item->$key;
            $it['name'] = $item->$text;
            $it['title'] = $item->$text;
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    public function buildCheckboxOptions($label = 'name', $value = 'id')
    {
        $items = self::all();
        $newItemArr = [];
        foreach ($items as $item) {
            $it = [];
            $it['label'] = $item->$label;
            $it['value'] = $item->$value;
            $newItemArr[] = $it;
        }

        return $newItemArr;
    }

    // Can be deleted or not
    public function canDelete($idArr)
    {
        return true;
    }

    // Can be edit or not
    public function canUpdate($idArr)
    {
    }

    public function getDates()
    {
        return $this->dates;
    }

    // Frontend forms (database table field mapping)
    public function formFieldsMap()
    {
        return [];
    }

    // Convert Form Request To Input
    public function convertFormRequestToInput()
    {
        $req = request();
        $fieldMap = $this->formFieldsMap();

        foreach ($fieldMap as $inputField => $tbField) {
            if ($req->has($inputField)) {
                $srcValue = $req->input($inputField);
                if ($srcValue == 0 || $srcValue == '0') {
                    $input[$tbField] = $srcValue;
                }

                if ($srcValue === false || ! empty($req->input($inputField, ''))) {
                    $input[$tbField] = $req->input($inputField);
                }
            }
        }

        return $input;
    }

    // Return to Data Sheet
    public function getTable()
    {
        // $data = Schema::getColumnListing($table);
        return parent::getTable();
    }

    public static function staticGetConnectionName()
    {
        return (new self)->getConnectionName();
    }

    // Refresh, calculate item
    public function computeItem($id)
    {
        return $id;
    }

    // Clear table data
    protected function clearData()
    {
    }

    // Get native SQL queries
    public function getRawSqlQuery()
    {
        return true;
    }

    // Get column information
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
