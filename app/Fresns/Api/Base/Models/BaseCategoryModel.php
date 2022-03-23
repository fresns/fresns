<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Base\Models;

use App\Fresns\Api\Base\Config\BaseConfig;
use App\Fresns\Api\Helpers\CommonHelper;
use App\Fresns\Api\Helpers\TreeHelper;

// Scenes
// Scene 1: query all the ancestor categories of "c1"
// Scene 2: query all the descendants of "c1"
// Scene 3: determine whether "c1" and "c2" have a hierarchical relationship
class BaseCategoryModel extends BaseModel
{
    public $pageSize = BaseConfig::DEFAULT_ALL_IN_ONE_PAGE_SIZE;

    public function hookStoreAfter($id)
    {
        $currModel = get_class($this);
        $category = (new $currModel)->find($id);

        if (empty($category->parent_id)) { // Create root directory
            $category->level = 0; // Set the level to 0
            $category->path = '-'; // Set path to -
        } else {
            // Create non-root directory
            $category->level = $category->parent->level + 1; // Set the parent level to +1
            $category->path = $category->parent->path.$category->parent_id.'-'; // Set path to parent class path + parent class id
        }

        $category->save();

        return $id;
    }

    // Get the parent node of the upper level
    public function parent()
    {
        return $this->belongsTo(get_called_class());
    }

    // Get first-level child nodes
    public function children()
    {
        return $this->hasMany(get_called_class(), 'parent_id');
    }

    // Get all child nodes
    public function getAllChildren()
    {
        $result = [];
        $children = $this->children;

        foreach ($children as $child) {
            $child->key = $child->id;
            $child->value = $child->id;
            $child->title = $child->name;
            $result[] = $child;

            $childResult = $child->getAllChildren();
            foreach ($childResult as $subChild) {
                $result[] = $subChild;
            }
        }

        return $result;
    }

    /**
     * Get all ancestor category ids.
     */
    public function getPathIdsAttribute()
    {
        $path = trim($this->path, '-'); // Filter both ends of the -
        $path = explode('-', $path); // Cut into arrays with - as separator
        $path = array_filter($path); // Filter null values

        return $path;
    }

    /**
     * Get all ancestor categories and sort them in positive order by hierarchy.
     */
    public function getAncestorsAttribute()
    {
        return BaseCategoryModel::query()
            ->whereIn('id', $this->path_ids) // Call "getPathIdsAttribute" to get the ancestor class id
            ->orderBy('level') // By Level
            ->get();
    }

    /**
     * Get all ancestor class names and the current class name.
     */
    public function getFullNameAttribute()
    {
        return $this->ancestors // Call "getAncestorsAttribute" to get the ancestor class
        ->pluck('name') // The "name" column of all ancestor classes as an array
        ->push($this->name) // Append the "name" column of the current category to the end of the array
        ->implode(' - '); // Assemble the values of the array into a string using the "-" symbol
    }

    // Get "childrenIds"
    public function getAllChildrenIds(&$childrenIdArr)
    {
        $children = $this->getAllChildren();
        foreach ($children as $child) {
            $childrenIdArr[] = $child->id;
            if (! empty($child->children)) {
                $this->getAllChildrenIds($childrenIdArr);
            }
        }
    }

    // To delete the previous operation, find the subcategories of each category and delete them
    public function hookDestroyBefore($idArr)
    {
        $currClass = get_called_class();
        $currModel = new $currClass;

        // Get all ids to be deleted
        $allChildrenIds = [];
        foreach ($idArr as $id) {
            $category = $currModel->find($id);
            $allChildren = $category->getAllChildren();
            $allChildrenArr = CommonHelper::objectToArray($allChildren);
            TreeHelper::getAllIdsInTreeData($allChildrenArr, $allChildrenIds);
        }

        $allNeedDestroyIdArr = array_unique(array_merge($idArr, $allChildrenIds));

        // Delete one by one
        foreach ($allNeedDestroyIdArr as $id) {
            $this->hookDestroyItemBefore($id);
            $currModel->find($id)->delete();
        }
    }

    // Is it possible to delete
    public function canDelete($idArr)
    {
        return parent::canDelete($idArr);
    }
}
