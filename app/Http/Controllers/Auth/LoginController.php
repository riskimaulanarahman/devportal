<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Symfony\Component\HttpFoundation\Request;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Submission\Project;

class LoginController extends Controller
{
    use AuthenticatesUsers, ListensForLdapBindFailure;

    protected $redirectTo = '/';

    public function username()
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        //
    }

    protected function credentials(Request $request)
    {

        return [
            'samaccountname' => $request->username,
            'password' => $request->password,
            'fallback' => [
                'email' => $request->email,
                'password' => $request->password,
            ],
        ];

    }

    public function showLoginForm()
    {
        $waiting = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('tbl_developer','tbl_assignment.developer_id','tbl_developer.id')
                ->where('requestStatus',3)
                ->where('projectStatus','Waiting')
                ->get();
        $progress = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('tbl_developer','tbl_assignment.developer_id','tbl_developer.id')
                ->where('requestStatus',3)
                ->where('projectStatus','Progress')
                ->get();
        $Completed = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('tbl_developer','tbl_assignment.developer_id','tbl_developer.id')
                ->where('requestStatus',3)
                ->where('projectStatus','Completed')
                ->get();
        
        $project = [
            "waiting" => $waiting,
            "progress" => $progress,
            "completed" => $Completed
        ];

        $newProject = [];

        function processProjectItem($item, &$newProject) {
            $id = $item['id'];
            $fullname = $item['fullname'];
            $nameSystem = $item['nameSystem'];
            $progress = $item['progress'];
            $projectStatus = $item['projectStatus'];
            $initials = $item['initials'];

            if (!isset($newProject[$id])) {
                $newProject[$id] = [
                    'id' => $id,
                    'fullname' => $fullname,
                    'nameSystem' => $nameSystem,
                    'progress' => $progress,
                    'projectStatus' => $projectStatus,
                    'initials' => [$initials]
                ];
            } else {
                $newProject[$id]['initials'][] = $initials;
            }
        }

        $waitingProjects = [];
        $progressProjects = [];
        $completedProjects = [];

        foreach ($project['waiting'] as $item) {
            processProjectItem($item, $waitingProjects);
        }
        foreach ($project['progress'] as $item) {
            processProjectItem($item, $progressProjects);
        }
        foreach ($project['completed'] as $item) {
            processProjectItem($item, $completedProjects);
        }

        $newProject = [
            'waiting' => array_values($waitingProjects),
            'progress' => array_values($progressProjects),
            'completed' => array_values($completedProjects)
        ];

        return view('auth.login')->with('project', $newProject);

    }

    // public function logout(Request $request)
    // {
    //     // Clear session data from the database
    //     DB::table('sessions')->where('user_id', auth()->id())->delete();

    //     // Log out the user
    //     $this->guard()->logout();

    //     $request->session()->invalidate();

    //     return redirect()->intended($this->redirectTo);

    // }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->listenForLdapBindFailure();
    }

}