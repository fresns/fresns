<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Traits;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Route;

trait HookModelTrait
{
    public $model;

    public function setModel($m)
    {
        $this->model = $m;
    }

    // Hook functions: Initializing the database
    public function hookConnectionInit()
    {
        return true;
    }

    // Hook functions: Model initialization
    public function hookModelInit()
    {
        return true;
    }

    // Hook functions: Model additional table condition initialization
    // Scenario: Schedule search criteria
    public function hookModelInitAppend()
    {
        return true;
    }

    // Hook functions: After the model has been created
    public function hookStoreAfter($id)
    {
        return $id;
    }

    // Hook functions: Before Batch Delete
    public function hookDestroyBefore($idArr)
    {
        foreach ($idArr as $id) {
            $this->hookDestroyItemBefore($id);
        }

        return $idArr;
    }

    // Hook functions: Before individual deletion
    public function hookDestroyItemBefore($id)
    {
        return $id;
    }
}
