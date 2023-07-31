<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;


trait HasGetModule {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    public function getModuleId($moduleName) 
    {
        $module = $this->module->select('id', 'module')->where('module', $moduleName)->first();
        if ($module) {
            return $module->id;
        }
        return null;
    }

}