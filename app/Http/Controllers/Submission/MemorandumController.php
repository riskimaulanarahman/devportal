<?php

namespace App\Http\Controllers\Submission;

use DB;
use COM;
use Log;
use App\Models\User;
use App\Models\Module;

use App\Models\Employee;
use App\Models\Attachment;
use App\Models\Useraccess;
use App\Mail\SubmissionMail;
use App\Models\Approvaluser;
use Illuminate\Http\Request;
use App\Models\MemorandumHis;
use Illuminate\Support\Carbon;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\MemorandumReq;

use function PHPUnit\Framework\fileExists;

class MemorandumController extends Controller
{
    public $model;
    public $modulename;
    public $module;
    public $user;
    public $codename;

    public function __construct()
    {
        $this->model = new MemorandumReq();
        $this->modulename = 'MemorandumReq';
        $this->codename = 'Memorandum';
        $this->module = new Module();
        $this->user = new User();
    }

    public function index(Request $request)
    {
        try {
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employeeid = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;
            $historycontract = MemorandumHis::all();
            $xpc = DB::table('memoView')->get();
            // dd($xpc);

            $dataquery = $this->model->query();

            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end 
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id 
                where l.ApprovalAction='1' 
                and l.req_id = request_memorandum.id and l.module_id = '".$module_id."' 
                and request_memorandum.requestStatus='1'
                order by a.sequence)"; 

            $getAssignment = "(select top 1
            CASE WHEN user_id='".$user_id."' then 1 else 0 end
            from
            (select
            u.id as user_id,
            u.fullname as nama_users
            from tbl_assignment l
            left join employee.tbl_employee e on l.employee_id = e.id
            left join users u on e.LoginName = u.username
            where l.req_id = request_memorandum.id
            and l.module_id = '".$module_id."') as tab1
            where user_id = '".$user_id."')";

            $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
            $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;      
            $data = $dataquery
                ->selectRaw("request_memorandum.id,
                    request_memorandum.user_id,
                    request_memorandum.requestStatus,   
                    request_memorandum.employee_id,
                    request_memorandum.created_at,
                    request_memorandum.bu, 
                    request_memorandum.sysid, 
                    request_memorandum_his.sequence, 
                    employee.tbl_employee.FullName,
                    employee.tbl_employee.SAPID,
                    employee.tbl_employee.BirthOfDate, 
                    employee.tbl_level.Level,
                    employee.tbl_designation.DesignationName,
                    codes.code,
                    CASE WHEN request_memorandum.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes','request_memorandum.code_id','codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('request_memorandum_his', 'request_memorandum.sysid', '=', 'request_memorandum_his.sysid')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->leftJoin('tbl_assignment', function($join) use ($module_id) {
                    $join->on('request_memorandum.id', '=', 'tbl_assignment.req_id')
                        ->where('tbl_assignment.module_id', '=', $module_id);
                })
                ->leftJoin('employee.tbl_employee as emp', 'tbl_assignment.employee_id', '=', 'emp.id')
                // ->with(['user','approverlist', 'request_memorandum_his'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $getAllview) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $getAllview) {
                            if ($isAdmin) {
                                $query->whereIn("request_memorandum.requestStatus", [1,3,4])
                                    ->where("request_memorandum.user_id", "!=", $user_id);
                            } else if($getAllview) {
                                $query->whereIn("request_memorandum.requestStatus", [3])
                                ->where("request_memorandum.user_id", "!=", $user_id);
                            } else {
                                $query->where("request_memorandum.user_id", "!=", $user_id)
                                ->whereIn("request_memorandum.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_memorandum.user_id", $user_id);
                })
                ->where(function ($query) use ($user_id,$getAssignment, $isAdmin, $getAllview, $subquery) {
                    if(!$isAdmin) {
                        if(!$getAllview) {
                            $query->whereRaw($getAssignment . " = 1")
                            ->orWhere("request_memorandum.user_id", $user_id)
                            ->orWhereRaw($subquery . " = 1");
                        }
                    }
                })
                ->groupBy('request_memorandum.id',
                    'request_memorandum.user_id',
                    'request_memorandum.requestStatus',
                    'request_memorandum.created_at',
                    'request_memorandum.employee_id',
                    'request_memorandum.bu',
                    'request_memorandum.sysid',
                    'request_memorandum_his.sequence',
                    'codes.code',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.SAPID',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                    'employee.tbl_employee.deptheadName',
                )                
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_memorandum.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_memorandum.created_at desc")
                ->get();
                $sysids = $data->pluck('sysid')->unique()->toArray();
                $memorandumHistories = DB::table('request_memorandum_his')
                ->whereIn('sysid', $sysids)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('sysid');
                foreach ($data as $row) {
                    $row->memorandumHistories = $memorandumHistories->get($row->employee_id, collect())->values();
                }
                // dd($data);
           
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            // $requestData['user_id'] = $this->getAuth()->id;
            $requestData['sysid'] = $this->getEmployeeID()->sys_id;
            $requestData['employee_id'] = $this->getEmployeeID()->id;
            $requestData['depthead_id'] = $this->getDeptheadbyIDemployee($this->getEmployeeID()->id);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);
            // dd($newData);

            // Simpan id dari data baru
            $req_id = $newData->id;

            $this->createApprManager($requestData['depthead_id'], $this->modulename, $req_id);

            DB::commit();

            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $user_id = $this->getAuth()->id;
        $module_id = $this->getModuleId($this->modulename);

        $subquery = "(select TOP 1 CASE WHEN a.user_id='" . $user_id . "'  then 1 else 0 end 
            from tbl_approverListReq l
            left join tbl_approver a on l.approver_id=a.id
            left join tbl_approvaltype r on a.approvaltype_id = r.id
            where l.ApprovalAction='1' and l.req_id = request_memorandum.id and l.module_id = '" . $module_id . "' and request_memorandum.requestStatus='1'
            order by a.sequence)";

        $checkUserAccess = Useraccess::where('module_id', $module_id)->where('employee_id', $user_id)->first();
        $getAllview = ($checkUserAccess) ? $checkUserAccess->allowView : null;

        try {
            // Ambil data request_memorandum
            $data = $this->model
                ->select(
                    'request_memorandum.*',
                    'codes.code',
                    'employee.tbl_employee.FullName',
                    'employee.tbl_employee.sys_id',
                    'employee.tbl_employee.SAPID', 
                    'employee.tbl_employee.JoinDate', 
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_employee.contract_status',
                    'employee.tbl_employee.BirthOfDate',
                    'employee.tbl_employee.JoinDate',
                    'employee.tbl_employee.deptheadName',
                    'employee.tbl_level.Level',
                    'employee.tbl_designation.DesignationName',
                )
                ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
                ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
                ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
                ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
                ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
                ->where('request_memorandum.id', $id)
                ->first();

            if (!$data) {
                return response()->json(["status" => "error", "message" => "Data tidak ditemukan"], 404);
            }

            // Jika code_id null, maka generate kode baru
            if ($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }

            // Tambahkan atribut ismine
            $data->ismine = ($data->employee_id == $user_id);

            // Tambahkan atribut ispendingonme
            $data->isPendingOnMe = $this->model
                ->selectRaw($subquery . " as isPendingOnMe")
                ->where('id', $id)
                ->first()
                ->isPendingOnMe;        

                $sysid = $data->sysid;
                $memorandumHistories = DB::table('request_memorandum_his')
                    ->where('sysid', $sysid)
                    ->get();                
                $data->memorandumHistories = $memorandumHistories;
                // dd($data);
            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data, // Data utama request_memorandum
                // 'memorandumHistories' => $memorandumHistories // Semua data dari request_memorandum_his
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        
        try {
            $requestData = $request->all();
            $data = $this->model->findOrFail($id);
            $data->update($requestData);
            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error", 
                "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
 
            // Cari module berdasarkan nama modul
            $module = $this->module->select('id', 'module')->where('module', $this->modulename)->first();
            $user_id = $this->getAuth()->id;

            // Jika module ditemukan, lakukan delete secara atomik
            if ($module) {
                DB::transaction(function () use ($id, $module, $user_id) {
                    // Hapus data pada tabel ApproverListReq
                    ApproverListReq::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    ApproverListHistory::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();

                    // Hapus data pada tabel utama
                    
                    $data = $this->model->where('id',$id)->where('requestStatus',0)->where('user_id',$user_id)->first();
                    if ($data) {
                        $data->delete();
                    } else {
                        throw new \Exception($this->getMessage()['errordestroysubmission']);
                    }
                    
                });

                return  response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

            } else {
                return  response()->json(["status" => "error", "message" => $this->getMessage()['modulenotfound']]);
            }

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function genPdfmemorandumReq(Request $request, $id)
    {
        $dataAppr = DB::table('memoApprover')->select('*')->where('id',$id)->get(); // data approver
        // \Log::info('Starting genPdfmemorandumReq', ['id' => $id]);
        $data = $this->model->select(
                'request_memorandum.*',
                'codes.code',
                'users.username',
                'employee.tbl_employee.FullName as FullName', 
                'employee.tbl_employee.sys_id as sys_id', 
                'employee.tbl_employee.SAPID as SAPID',
                'employee.tbl_employee.JoinDate as JoinDate', 
                'employee.tbl_employee.deptheadName as deptheadName',
                'employee.tbl_employee.contract_status as contract_status',
                'employee.tbl_employee.BirthOfDate as BirthOfDate',
                'employee.tbl_level.Level as Level',
                'employee.tbl_designation.DesignationName as DesignationName',
            )
            ->leftJoin('users', 'request_memorandum.user_id', 'users.id')
            ->leftJoin('codes', 'request_memorandum.code_id', '=', 'codes.id')
            ->leftJoin('employee.tbl_employee', 'request_memorandum.employee_id', '=', 'employee.tbl_employee.id')
            ->leftJoin('employee.tbl_location', 'employee.tbl_employee.location_id', '=', 'employee.tbl_location.id')
            ->leftJoin('employee.tbl_level', 'employee.tbl_employee.level_id', '=', 'employee.tbl_level.id')
            ->leftJoin('employee.tbl_designation', 'employee.tbl_employee.designation_id', '=', 'employee.tbl_designation.id')
            ->leftJoin('request_memorandum_his as rm_his', 'request_memorandum.id', '=', 'rm_his.req_id')
            ->where('request_memorandum.id', $id)
            ->first();
        if (!$data || !$data->approverHistory) {
            return response()->json(["status" => "error", "message" => "Data or approver history not found"]);
        }
        $contracts = DB::table('request_memorandum_his as rm_his')
        ->select('rm_his.sequence', 
        'rm_his.startContract', 
        'rm_his.endContract',
        'rm_his.remarks')
        ->where('rm_his.req_id', $id)
        ->get();

        if ($contracts->isEmpty()) {
            \Log::warning('No contracts found for request ID: ' . $id);
        }
        $today = Carbon::today();
        $oldContracts = []; 
        $currentContracts = []; 
        $newContracts = []; 
        foreach ($contracts as $contract) {
            if ($contract->endContract < $today) {
                // Kontrak Lama: Berakhir sebelum hari ini
                $oldContracts[] = $contract;
            } elseif ($contract->startContract <= $today && $contract->endContract >= $today) {
                // Kontrak Sekarang: Sedang berlangsung hari ini
                $currentContracts[] = $contract;
            } elseif ($contract->startContract > $today) {
                // Kontrak Baru: Dimulai setelah hari ini
                $newContracts[] = $contract;
            }
        }
        try {
            $birthDate = Carbon::parse($data->BirthOfDate);
            $usia = $birthDate 
                ? Carbon::now()->year - $birthDate->year 
                    - (Carbon::now()->lessThan(Carbon::parse($birthDate)->addYears(Carbon::now()->year - $birthDate->year)) ? 1 : 0)
                : null;
            $excel = new \COM("Excel.Application") or die("ERROR: Unable to instantiate COM!\r\n");
            $excel->Visible = false;
            $file = public_path("template/memo/memos.xlsx");
            if (!file_exists($file)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File not found: ' . $file
                ]);
            }
            $Workbook = $excel->Workbooks->Open($file, false, true);
            $Worksheet = $Workbook->Worksheets(1);
            $Worksheet->Activate;
            $Worksheet->Range("B10")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E15")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E18")->Value = $data->FullName ?? ''; 
            $Worksheet->Range("F18")->Value = $data->SAPID ?? ''; 
            $Worksheet->Range("E21")->Value = $data->DesignationName ?? ''; 
            $Worksheet->Range("F21")->Value = $data->sys_id ?? ''; 
            $Worksheet->Range("C36")->Value = $data->remarks ?? ''; 
            $Worksheet->Range("D36")->Value = $data->remarks ?? ''; 
            $Worksheet->Range("C34")->Value = $data->remarks ?? ''; 
            $Worksheet->Range("D20")->Value = $data->remarks ?? ''; 
            $Worksheet->Range("C63")->Value = $data->DesignationName ?? ''; 
            $Worksheet->Range("A56")->Value = $data->username ?? ''; 
            $Worksheet->Range("C56")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("E39")->Value = $data->deptheadName ?? ''; 
            $Worksheet->Range("C62")->Value = $data->Pendidikan ?? '-'; 
            $Worksheet->Range("G11")->Value = $data->code ?? '-'; 
            $Worksheet->Range("C64")->Value = $data->Usia ?? '-'; 
            $Worksheet->Range("C61")->Value = $data->BirthOfDate 
                ? Carbon::parse($data->BirthOfDate)->locale('id')->translatedFormat('j F Y')
                : '';
            $Worksheet->Range("C62")->Value = $usia ?? 'N/A';
            
            $Worksheet->Range("E24")->Value = $data->JoinDate 
                ? Carbon::parse($data->JoinDate)->locale('id')->translatedFormat('j F Y')
                : '';
            $Worksheet->Range("G10")->Value = $data->created_at 
                ? Carbon::parse($data->created_at)->locale('id')->translatedFormat('j F Y')
                : '';
            $startRow = 61; // Baris awal untuk kontrak
            $filteredContracts = array_merge($oldContracts, $currentContracts); // Gabungkan kontrak lama dan sekarang
            foreach ($filteredContracts as $index => $contract) {
                $row = $startRow + $index * 1;
                $Worksheet->Range("E{$row}")->Value = "Kontrak " . ($index + 1); // Label Kontrak
                $Worksheet->Range("F{$row}")->Value = $contract->startContract && $contract->endContract
                    ? Carbon::parse($contract->startContract)->format('d-m-Y') . ' - ' . Carbon::parse($contract->endContract)->format('d-m-Y')
                    : ' - '; // Periode Kontrak
            }
            if (!empty($currentContracts)) {
                $currentEndContract = $currentContracts[0]->endContract; // Ambil endContract dari kontrak aktif pertama
                $Worksheet->Range("E27")->Value = Carbon::parse($currentEndContract)->locale('id')->translatedFormat('j F Y'); // Format menjadi dd-month(string)-yyyy
            } else {
                $Worksheet->Range("E27")->Value = ' - '; // Jika tidak ada kontrak aktif
            }
            if (!empty($newContracts)) {
                $newStartContract = $newContracts[0]->startContract; // Ambil startContract dari kontrak baru pertama
                $newEndContract = $newContracts[0]->endContract; // Ambil endContract dari kontrak baru pertama
                $Worksheet->Range("D33")->Value = Carbon::parse($newStartContract)->locale('id')->translatedFormat('j F Y') . ' sampai dengan ' . Carbon::parse($newEndContract)->locale('id')->translatedFormat('j F Y'); // Isi di D33
            } else {
                $Worksheet->Range("D33")->Value = ' - '; // Jika tidak ada kontrak baru
            }

            $picpath = public_path("assets/images/approved.png");

            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel, $offset = 0) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top - $offset; // Offset untuk menggeser gambar ke atas
                $pic->Left = $excel->Cells($row, $column)->Left;
            }

            foreach ($dataAppr as $appr) {
                if ($appr->sequence == 1) {
                    if ($appr->approvalAction == 3) {
                        // $Worksheet->Range("A53")->Value = $appr->apprname;
                        // $Worksheet->Range("A54")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 55, 1, 40, $excel, 25);
                    }
                }
                if ($appr->sequence == 2) {
                    if ($appr->approvalAction == 3) {
                        // $Worksheet->Range("E56")->Value = $appr->apprname;
                        // $Worksheet->Range("E57")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 55, 3, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 4, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 5, 40, $excel, 25);
                        addPictureToWorksheet($Worksheet, $picpath, 55, 7, 40, $excel, 25);
                    }
                }
            }
            $xlTypePDF = 0;
            $xlQualityStandard = 0;
            $code_sanitized = str_replace('/', '_', $data->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/memo/pdf/' . $fileName);
			$path = $filePath;
			if (file_exists($path)) {
				unlink($path);
			}
			$Worksheet->ExportAsFixedFormat($xlTypePDF, $path, $xlQualityStandard);
			
			$excel->CutCopyMode = false;
			$Workbook->Close(false);
			unset($Worksheet);
			unset($Workbook);
			$excel->Workbooks->Close();
			$excel->Quit();
			unset($excel);
			
            $pathfilename = 'public/template/memo/pdf/' . $fileName;
            DB::table('request_memorandum_his')
            ->where('req_id', $id) // Sesuaikan dengan primary key di tabel
            ->update(['approveddoc' => $pathfilename]);
            $this->processcopy($pathfilename);

			return $pathfilename;
        } catch (\Exception $e) {
            // Logging error
            $this->logerror($request->ip(), $request->url(), 'gen-pdf-memorandum', $e->getMessage());
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}