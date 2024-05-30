<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Submission\Project;

trait ProcessProjectTrait {

    public function processProjects()
    {
        $waiting = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','reference.tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('reference.tbl_developer','tbl_assignment.developer_id','reference.tbl_developer.id')
                ->where('requestStatus',3)
                ->where('tbl_assignment.module_id',36)
                ->where('projectStatus','Waiting')
                ->get();
        $progress = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','reference.tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('reference.tbl_developer','tbl_assignment.developer_id','reference.tbl_developer.id')
                ->where('requestStatus',3)
                ->where('tbl_assignment.module_id',36)
                ->where('projectStatus','Progress')
                ->get();
        $Completed = Project::select('request_project.id','users.fullname','nameSystem','progress','projectStatus','reference.tbl_developer.initials')
                ->leftJoin('users','request_project.user_id','users.id')
                ->leftJoin('tbl_assignment','request_project.id','tbl_assignment.req_id')
                ->leftJoin('reference.tbl_developer','tbl_assignment.developer_id','reference.tbl_developer.id')
                ->where('requestStatus',3)
                ->where('tbl_assignment.module_id',36)
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

        return $newProject;
    }

}