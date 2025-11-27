<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use App\Models\SideMenu;
use App\Models\Sequence;
use App\Models\Useraccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneratemenuController extends Controller
{
    public function index($route)
    {
        $sidemenu = SideMenu::select(['id','title','icon_id','sequence_id','parent_id','is_active','is_admin','companyList','route'])
            ->where('route',$route)
            ->first();
 
        if(!$sidemenu) {
            abort(404);
        }

        $sequence = Sequence::select(['title','is_active'])
            ->where('id',$sidemenu->sequence_id)
            ->first();

        $icon = Icon::select('name')->where('id',$sidemenu->icon_id)->first();

        $menu = [];
        if ($sequence && ($sequence->is_active || $sidemenu->is_active)) {
            $menu = [
                'icon'   => $icon ? $icon->name : null,
                'module' => $sequence->title,
                'title'  => $sidemenu->title,
                'active' => $sidemenu->is_active,
                'menu'   => []
            ];
        }

        $secondary_menu = SideMenu::select(['id','icon_id','title','route'])
            ->whereRaw('parent_id like ?',[$sidemenu->id])
            ->get();

        if (count($secondary_menu) > 0) {
            foreach ($secondary_menu as $key => $item_menu) {
                $icon_menu = Icon::select('name')->where('id',$item_menu->icon_id)->first();
                $menu['menu'][] = [
                    'icon'    => $icon_menu ? $icon_menu->name : null,
                    'title'   => $item_menu->title,
                    'route'   => $item_menu->route,
                    'submenu' => [],
                    'data'    => null
                ];
                $submenu = SideMenu::select(['id','icon_id','title','route','is_active'])
                    ->whereRaw('parent_id like ? and is_secondary_menu like 1',[$item_menu->id])
                    ->get();
                if (count($submenu) > 0) {
                    foreach ($submenu as $item_submenu) {
                        $icon_submenu = Icon::select('name')->where('id',$item_submenu->icon_id)->first();
                         $menu['menu'][$key]['submenu'][] = [
                            'icon'   => $icon_submenu ? $icon_submenu->name : null,
                            'title'  => $item_submenu->title,
                            'route'  => $item_submenu->route,
                            'active' => $item_submenu->is_active,
                            'data'   => null
                        ];
                    }
                } else {
                    $menu['menu'][$key]['submenu'] = null;
                }
            }
        }

        $viewGrid = ($sidemenu->sequence_id !== 2)
            ? view('grid.index')->with('data',$menu)
            : redirect()->route($route);

        if ($menu != null) {
            if (isset($this->getAuth()->isAdmin)) {
                // $user = $this->getAuth();
                // $employee = DB::table('employee.tbl_employee')
                //               ->where('LoginName',$user->name ?? $user->username ?? null)
                //               ->first();
                // $isAdmin = intval($user->isAdmin ?? 0);
                // $isPIC   = $employee ? intval($employee->isPIC ?? 0) : 0;

                // // === Override khusus SPKL ===
                // if ($sidemenu->route === '#spkl' || str_starts_with($sidemenu->route,'spkl_')) {
                //     if ($isAdmin === 1) {
                //         return $viewGrid;
                //     } elseif ($isAdmin === 0 && $isPIC === 1) {
                //         return $viewGrid;
                //     } else {
                //         return view('errors.401');
                //     }
                // }

                if ($this->getAuth()->isAdmin == 1) {
                    return $viewGrid;
                } else if ($this->getAuth()->isAdmin == 0) {
                    if ($sidemenu->is_admin == 0) {
                        $empBU = $this->getEmployeeID()->companycode;
                        if ($sidemenu->companyList == null || $sidemenu->companyList == "") {
                            return $viewGrid;
                        } else {
                            $buArray = explode(",",$sidemenu->companyList);
                            if (!in_array($empBU,$buArray)) {
                                return view('errors.401');
                            } else {
                                return $viewGrid;
                            }
                        }
                    } else {
                        $checkaccess = Useraccess::join('reference.side_menus','authorization.tbl_useraccess.module_id','reference.side_menus.modules')
                            ->where('employee_id',$this->getAuth()->id)
                            ->where('allowView',true)
                            ->get();
                        if ($checkaccess) {
                            foreach ($checkaccess as $key) {
                                if ($key->route == $route) {
                                    return $viewGrid;
                                }
                            }
                        }
                    }
                    return view('errors.401');
                }
            } else {
                return redirect('/');
            }
        } else {
            return view('errors.404');
        }
    }
}
