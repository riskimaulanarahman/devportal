<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

use App\Models\Session as Sess;
use App\Models\Theme;
use App\Models\User;

class HomeController extends Controller
{
    private $sess;
    private $theme;
    private $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->sess = new Sess();
        $this->theme = new Theme();
        $this->user = new User();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    public function root()
    {
        
        $this->sess->where('id', session()->getId())->update(['last_login' => now()]);

        $user = Auth::user(); // get user auth data
        $ipAddress = request()->ip(); // get ip address
        $login = $this->sess->where('user_id', Auth::id())->orderBy('last_login','desc')->first();
        // add theme
        $checkuserintheme = $this->theme->where('user_id',$user->id)->get();
        if(count($checkuserintheme) < 1) {
            $updtheme = $this->theme->create([
                'user_id' => $user->id
            ]);
            
            $this->user->where('id',$user->id)->update([
                'theme_id' => $updtheme->id
            ]);

        }
        // add theme

        // calculate date
        $now = Carbon::now();
        $dateStringsession = $login->last_login;
        $datesession = Carbon::createFromFormat('Y-m-d H:i:s.u', $dateStringsession);
        $d1= $now->format('Y-m-d H:i:s');
        $d2= $datesession->format('Y-m-d H:i:s');
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $d1);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $d2);
        $diff=abs($date1->getTimestamp() - $date2->getTimestamp())/60;
        // calculate date
    
        if (($diff <= 60) && ($login->ip_address)!=$ipAddress) { // if session not expired and when ip address !== last login
            Auth::logout();    
            session()->flash('error', "You are Logged in on other devices. ( ".$login->ip_address." )");
            return redirect('login');
        } else {
            if (($diff>60) || ($login->ip_address==$ipAddress)){ // if session expired or when ip address == last login
                $this->sess->where('user_id',$user->id)
                ->update([
                    'user_id' => null
                ]);
            }	
        }

        // dashboard section

        $tables = [
            'request_project' => $this->getModuleId('Project'), 
            'request_uavmission' => $this->getModuleId('UavMission'),
            'request_ticket' => $this->getModuleId('Ticket'),
            'request_hrsc' => $this->getModuleId('Hrsc'),
            'request_mom' => $this->getModuleId('Mom'),
            'request_jdi' => $this->getModuleId('Jdi'),
            'request_it_activedirectory' => $this->getModuleId('ActiveDirectory'),
            'request_mmf' => $this->getModuleId('Mmf'),
            'request_material' => $this->getModuleId('MaterialReq'),
            'request_ccm' => $this->getModuleId('Ccm'),
            'request_ghm' => $this->getModuleId('Ghm'),
            'request_capex' => $this->getModuleId('Capex'),
            // 'request_memorandum' => $this->getModuleId('Memorandum'),
            'request_legal' => $this->getModuleId('Legal'),
        ]; 
        // masukan nama table dan module_id dari table tersebut
        
        $user_id = $this->getAuth()->id;
        $totalPendingSubmission = 0;
        $listPendingSubmission = [];
        $totalPendingApproval = 0;
        $listPendingApproval = [];
        $codeIds = array();

        // list pending submissions
        foreach ($tables as $table => $module_id) {
            if($table == 'request_mmf') {
                $mmfSelect = ',category';
            } else {
                $mmfSelect = '';
            }

            $results = DB::table($table)
                ->selectRaw($table.".code_id,codes.code,users.fullname".$mmfSelect."
                ")
                ->leftJoin('codes', $table.'.code_id', '=', 'codes.id')
                ->leftJoin('tbl_approverListReq', function($join) use ($table, $module_id) {
                    $join->on($table.'.id', '=', 'tbl_approverListReq.req_id')
                         ->where('tbl_approverListReq.module_id', '=', $module_id)
                         ->where('tbl_approverListReq.approvalAction','=',1);
                })
                ->leftJoin('tbl_approver', 'tbl_approverListReq.approver_id', '=', 'tbl_approver.id')
                ->leftJoin('users', 'tbl_approver.user_id', '=', 'users.id')
                ->where('requeststatus', 1)
                ->where($table.'.user_id', $this->getAuth()->id)
                ->orderBy('tbl_approver.sequence','desc')
                ->get();
            $count = count($results);

            if ($count > 0) {
                foreach ($results as $result) {
                    if($table == 'request_mmf') {
                        $mmftype = ($result->category == 'MMF30') ? '30' : '28';
                    }
                    $url = '';
                    if ($table) {
                        switch ($table) {
                            case 'request_project':
                                $url = 'project_request';
                                break;
                            case 'request_uavmission':
                                $url = 'uavmission_request';
                                break;
                            case 'request_ticket':
                                $url = 'ticket_request';
                                break;
                            case 'request_hrsc':
                                $url = 'hrsc_request';
                                break;
                            case 'request_mom':
                                $url = 'mom_request';
                                break;
                            case 'request_jdi':
                                $url = 'jdi_request';
                                break;
                            case 'request_it_activedirectory':
                                $url = 'ad_request';
                                break;
                            case 'request_mmf':
                                $url = ($mmftype == '30') ? 'mmf_30_request' : 'mmf_28_request';
                                break;
                            case 'request_material':
                                $url = 'material_request';
                                break;
                            case 'request_ccm':
                                $url = 'ccm_request';
                                break;
                            case 'request_ghm':
                                $url = 'ghm_request';
                                break;
                            // case 'request_memorandum':
                            //     $url = 'memorandum_request';
                            //     break;
                            case 'request_legal':
                                $url = 'legal_request';
                                break;
                            // case 'request_capex':
                            //     $url = 'request_capex';
                            //     break;
                            // Tambahkan case sesuai dengan url module
                        }
                    }

                    // Menambahkan URL pada response JSON
                    $listPendingSubmission[$table][$result->code_id]['code_id'] = $result->code_id;
                    $listPendingSubmission[$table][$result->code_id]['code'] = $result->code;
                    $listPendingSubmission[$table][$result->code_id]['waitingapprover'][0]['fullname'] = $result->fullname;
                    $listPendingSubmission[$table][$result->code_id]['url'] = $url;

                    if (!in_array($result->code, $codeIds)) {
                        $totalPendingSubmission++;
                        $codeIds[] = $result->code;
                    }
                }
            }
        }

        // list need your approval
        foreach ($tables as $table => $module_id) {
            if($table == 'request_mmf') {
                $mmfSelect = ',category';
            } else {
                $mmfSelect = '';
            }

            $subquery = "(select TOP 1 CASE WHEN a.user_id='".$user_id."'  then 1 else 0 end 
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id 
                where l.ApprovalAction='1' and l.req_id = $table.id and l.module_id = '".$module_id."' and $table.requestStatus='1'
                order by a.sequence)";

            $results2 = DB::table($table)
                ->selectRaw("users.fullname as creator,codes.code,".$subquery." as isPendingOnMe".$mmfSelect."
                ")
                ->leftJoin('codes',$table.'.code_id','codes.id')
                ->leftJoin('users', $table.'.user_id', '=', 'users.id')
                ->whereRaw($subquery." = 1")
                ->orWhere("$table.user_id", $user_id)
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN $table.user_id = '".$user_id."' THEN 0 ELSE 1 END, $table.created_at desc")
                ->get();
                
                foreach ($results2 as $result2) {
                    if($table == 'request_mmf') {
                        $mmftype = ($result2->category == 'MMF30') ? '30' : '28';
                    }
                    $url = '';
                    if ($table) {
                        switch ($table) {
                            case 'request_project':
                                $url = 'project_request';
                                break;
                            case 'request_uavmission':
                                $url = 'uavmission_request';
                                break;
                            case 'request_ticket':
                                $url = 'ticket_request';
                                break;
                            case 'request_hrsc':
                                $url = 'hrsc_request';
                                break;
                            case 'request_mom':
                                $url = 'mom_request';
                                break;
                            case 'request_jdi':
                                $url = 'jdi_request';
                                break;
                            case 'request_it_activedirectory':
                                $url = 'ad_request';
                                break;
                            case 'request_mmf':
                                $url = ($mmftype == '30') ? 'mmf_30_request' : 'mmf_28_request';
                                break;
                            case 'request_material':
                                $url = 'material_request';
                                break;
                            case 'request_ccm':
                                $url = 'ccm_request';
                                break;
                            case 'request_ghm':
                                $url = 'ghm_request';
                                break;
                            // case 'request_memorandum':
                            //     $url = 'memorandum_approver';
                            //     break;
                            case 'request_legal':
                                $url = 'legal_request';
                                break;
                            // case 'request_capex':
                            //     $url = 'request_capex';
                            //     break;
                            // Tambahkan case sesuai dengan url module
                        }
                    }

                    // Menambahkan URL pada response JSON
                    $result2->url = $url;

                    if ($result2->isPendingOnMe == 1) {
                        $totalPendingApproval++;
                        $listPendingApproval[] = $result2;
                    }
                }
            }

        // return view and data
        return view('dashboard.index',compact(
            'totalPendingSubmission',
            'listPendingSubmission',
            'totalPendingApproval',
            'listPendingApproval'
        ));
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfilePicture(Request $request)
    {
        $picture = $request->file('picture');
        
        // Check if the uploaded file is an image
        if (!$picture->isValid() || !in_array($picture->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
            return response()->json(["status" => "error", "message" => $this->getMessage()['erroruploadimages']]);
        }
        
        $filename = Auth::user()->username . '.' . $picture->getClientOriginalExtension();
        $tujuan_upload = 'public\\upload\\profile';
        $picture->move($tujuan_upload,$filename);
        // $picture->move(public_path('/images/'), $filename);
        $source_file = $tujuan_upload.'\\'. $filename;

        // Update the path of the uploaded file to the avatar field of the currently authenticated user
        $user = Auth::user();
        $changeimage = User::findOrFail($user->id);
        $changeimage->avatar = $filename;
        $changeimage->save();

        $this->processcopy($source_file);

        return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}
