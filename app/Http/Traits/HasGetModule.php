<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\CategoryForm;

trait HasGetModule {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    public function getModuleId($moduleName) 
    {
        $module = Module::select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            return $module->id;
        }
        return null;
    }

    public function getModuleName($moduleID) 
    {
        $module = Module::select('id', 'module')->where('id', $moduleID)->first();
        if ($module) {
            return $module->module;
        }
        return null;
    }

    public function getCategoryFormIdByModule($moduleName) 
    {
        $categoryForm = CategoryForm::select('id', 'nameCategory')->where('nameCategory', $moduleName)->first();
        if ($categoryForm) {
            return $categoryForm->id;
        }
        return null;
    }

}