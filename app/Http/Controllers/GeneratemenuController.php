<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use App\Models\SideMenu;
use App\Models\Sequence;
use App\Models\Useraccess;
use Illuminate\Http\Request;

class GeneratemenuController extends Controller
{

    public function index($route)
    {

        $sidemenu = SideMenu::select(['id', 'title', 'icon_id', 'sequence_id', 'parent_id', 'is_active', 'is_admin', 'companyList'])
        ->where('route', $route)
        ->first();

        // jika page/route tidak ditemukan direct to 404 view
        if(!$sidemenu) {
            abort('404');
        }

        $sequence = Sequence::select(['title', 'is_active'])
        ->where('id', $sidemenu->sequence_id)
        ->first();
        $icon = Icon::select('name')->where('id', $sidemenu->icon_id)->first();
        $menu = array();
        // Check active menu or sequence
        if ($sequence->is_active || $sidemenu->is_active) {

            $menu = array(
                'icon' => $icon->name,
                'module' => $sequence->title,
                'title' => $sidemenu->title,
                'active' => $sidemenu->is_active,
                'menu' => array()
            );
            
        }
        // secondary menu queries
        $secondary_menu = Sidemenu::select(['id', 'icon_id', 'title', 'route'])->whereRaw('parent_id like ?', [$sidemenu->id])->get();
        // check secondary menu have value
        if (count($secondary_menu) > 0) {
            // Get secondary menu
            foreach ($secondary_menu as $key => $item_menu) {
                $icon_menu = Icon::select('name')->where('id', $item_menu->icon_id)->first();

                // push array in to menu
                array_push($menu['menu'], array(
                    'icon' => $icon_menu->name,
                    'title' => $item_menu->title,
                    'route' => $item_menu->route,
                    'submenu' => array(),
                    'data' => null
                ));

                // submenus queries
                $submenu = Sidemenu::select(['id', 'icon_id', 'title', 'route', 'is_active'])->whereRaw('parent_id like ? and is_secondary_menu like 1', [$item_menu->id])->get();
                // check have submenu item
                if (count($submenu) > 0) {
                    // get submenu
                    foreach ($submenu as $item_submenu) {
                        $icon_submenu = Icon::select('name')->where('id', $item_submenu->icon_id)->first();
                        // push array into sub menu
                        array_push(
                            $menu['menu'][$key]['submenu'],
                            array(
                                'icon' => $icon_submenu->name,
                                'title' => $item_submenu->title,
                                'route' => $item_submenu->route,
                                'active' => $item_submenu->is_active,
                                'data' => null

                            )
                        );
                    }
                } else {
                    $menu['menu'][$key]['submenu'] = null;
                }
            }
        };
        
        // return view
        $viewGrid = ($sidemenu->sequence_id !== 2) ? view('grid.index')->with('data', $menu) : redirect()->route($route); // kondisi jika section tidak sama dengan dashboard
        if($menu != null) {
            if(isset($this->getAuth()->isAdmin)) {
                    if($this->getAuth()->isAdmin == 1) {
                        return $viewGrid;
                    } else if ($this->getAuth()->isAdmin == 0) {
                        if($sidemenu->is_admin == 0){
                            $empBU = $this->getEmployeeID()->companycode;
                            if($sidemenu->companyList == null || $sidemenu->companyList == "") {
                                return $viewGrid;
                            } else {
                                $buString = $sidemenu->companyList;
                                $buArray = explode(",", $buString); // Mengubah string menjadi array berdasarkan pemisah koma
                                if (!in_array($empBU, $buArray)) { // Memeriksa apakah $sidemenu->companyList tidak ada dalam array $buArray
                                    return view('errors.401');
                                } else {
                                    return $viewGrid;
                                }
                            }
                        } else {
                            $checkaccess = Useraccess::join('reference.side_menus','authorization.tbl_useraccess.module_id','reference.side_menus.modules')
                            ->where('employee_id',$this->getAuth()->id) // employee_id disini maksudnya adalah user_id pada table users
                            ->where('allowView',true)
                            ->get();
                            if($checkaccess) {
                                foreach ($checkaccess as $key) {
                                    if($key->route == $route) {
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
