<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Base\Controllers;

use App\Fresns\Api\Base\Config\BaseConfig;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Helpers\CommonHelper;
use App\Fresns\Api\Traits\ApiTrait;
use App\Fresns\Api\Traits\HookControllerTrait;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use ApiTrait;
    use HookControllerTrait;

    protected $service;

    // List
    public function index(Request $request)
    {
        ValidateService::validateRule($request, $this->rules(BaseConfig::RULE_INDEX));

        $currentPage = $request->input('current', 1);

        $request->offsetSet('currentPage', $currentPage);

        $data = $this->service->searchData();

        $this->success($data);
    }

    // New
    public function store(Request $request)
    {
        $rules = $this->rules(BaseConfig::RULE_STORE);

        ValidateService::validateRule($request, $rules, $this->messages(BaseConfig::RULE_STORE));

        $this->hookStoreValidateAfter();

        $this->service->store();

        // Clear request data
        CommonHelper::removeRequestFields($this->service->getSearchableFields());

        $this->index($request);
    }

    // Update
    public function update(Request $request)
    {
        ValidateService::validateRule($request, $this->rules(BaseConfig::RULE_UPDATE));

        $this->hookUpdateValidateAfter();

        $id = $request->input('id');

        $this->service->update($id);

        // Clear request data
        CommonHelper::removeRequestFields($this->service->getSearchableFields());

        $this->index($request);
    }

    // Detail
    public function detail(Request $request)
    {
        ValidateService::validateRule($request, $this->rules(BaseConfig::RULE_DETAIL));

        $id = $request->input('id');
        $data = $this->service->detail($id);

        $this->success($data);
    }

    // Drop
    public function destroy(Request $request)
    {
        // Check Drop
        $ids = $request->input('ids');
        $idArr = explode(',', $ids);

        // Go drop
        $this->service->destroy($idArr);

        // Clear request data
        CommonHelper::removeRequestFields($this->service->getSearchableFields());

        $this->index($request);
    }

    // Validate rules
    public function rules($ruleType)
    {
        return [];
    }

    // Validate rule language
    public function messages($ruleType)
    {
        return [];
    }

    // Export
    public function export(Request $request)
    {
        $data = $this->service->exportData();
        $this->success($data);
    }

    // Import
    public function import(Request $request)
    {
        //ValidateService::validateRule($request, BaseConfig::IMPORT_RULE);

        $uploadFile = $request->file('excel');

        $path = $uploadFile->store('public/avatars');

        $storagePath = storage_path();
        $filePath = implode(DIRECTORY_SEPARATOR, [$storagePath, 'app', $path]);
        $parseInfo = $this->service->importData($filePath);

        $code = $parseInfo['code'] ?? ErrorCodeService::CODE_OK;
        $data = $parseInfo['data'] ?? ['default' => ''];

        // Success (return data)
        if ($code == ErrorCodeService::CODE_OK) {
            $this->success($data);
        }

        $msg = $parseInfo['msg'] ?? [];

        // Failure (return error message)
        $this->errorInfo($code, $msg, [], $data);
    }

    // Get Data
    public function fetch(Request $request)
    {
        $this->service->fetchData();
        $this->success();
    }
}
