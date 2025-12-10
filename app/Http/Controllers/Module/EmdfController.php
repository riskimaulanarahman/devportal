<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PersonalData;

class EmdfController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new PersonalData();
    }

    public function index()
    {
        try {

            $data = $this->model->all();

            return response()->json(["status" => "show", "message" => $this->getMessage()['show'] , 'data' => $data]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {

            $requestData = $request->all();

            $requestData['buyer_name'] = $this->getEmployeeByPG($request->pg)->FullName;
            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            
            $requestData = $request->all();
            if(isset($request->pg)) {
                $requestData['buyer_name'] = $this->getEmployeeByPG($request->pg)->FullName;
            }
            
            $data = $this->model->findOrFail($id);
            $data->update($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['update']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {

            $data = $this->model->findOrFail($id);
            $data->delete();

            return response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    protected function detailRelations(): array
    {
        return [
            'user:id,email',
            'communications' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'type',
                'number',
            ]),
            'addresses' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'address_type',
                'street_and_house_number',
                'city',
                'postal_code',
                'country',
                'tel_number',
                'name_contact_person',
            ]),
            'socialMedia' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'platform',
                'username',
            ]),
            'families' => function ($query) {
                $query->select($this->familySelectColumns());
            },
            'educations' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'education_establishment',
                'institute_location',
                'country',
                'start_date',
                'end_date',
                'certificate',
                'branch_of_study_major',
                'branch_of_study_minor',
            ]),
            'experiences' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'last_position_held',
                'company',
                'industry_type',
                'start_date',
                'end_date',
                'name_of_superior',
                'designation_of_superior',
                'last_drawn_salary',
                'reason_for_leaving',
            ]),
            'languages' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'language',
                'read',
                'write',
                'speak',
            ]),
            'skills' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'skill',
            ]),
            'sizes' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'height',
                'weight',
                'clothing_size',
                'pants_size',
                'shoe_size',
            ]),
            'banks' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'bank_name',
                'account_number',
                'payee',
                'bank_country',
                'branch_address',
            ]),
            'taxes' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'npwp',
                'registered_date',
                'npwp_address',
                'married_for_tax_purpose',
                'spouse_benefit',
                'number_of_dependents',
                'benefit_class',
                'jamsostek_id',
                'bpjs_id',
            ]),
            // 'documents' => fn ($query) => $query->select([
            //     'id',
            //     'personal_data_id',
            //     'type_document_id',
            //     'path',
            // ])->with([
            //     'typeDocument:id,name',
            // ]),
            'references' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'relation',
                'name',
                'number',
            ]),
        ];
    }

    public function genPdfEmdfReq(Request $request, $id) {
        $data =  $this->model->with($this->detailRelations())->find($id); // data candidate
        return $data;

        $originatorApproval = $data->approverHistory
            ->where('approvalType', 'Submitted')
            ->sortByDesc('approvalDate')
            ->first();
        
        $subimissionDate = ($originatorApproval) ? $originatorApproval->created_at : $data->created_at; // time originator submitted submission
        
        $emp = Employee::select('*')->with(['location','company','department'])->where('LoginName',$data->username)->first(); // data employee
        $dataAppr = DB::table('HcrfreqApprover')->select('*')->where('id',$id)->get(); // data approver

        try {
			$excel = new COM("Excel.Application") or die("ERROR: Unable to instantaniate COM!\r\n");
			$excel->Visible = false;

            $file = public_path("template/hris/hcrf/hcrf.xlsx");

			$Workbook = $excel->Workbooks->Open($file, false, true) or die("ERROR: Unable to open " . $file . "!\r\n");
			$Worksheet = $Workbook->Worksheets(1);
			$Worksheet->Activate;


            // Fungsi untuk membersihkan dan mengkonversi HTML menjadi teks plain dengan newline
            function cleanAndConvertHTML($text) {
                // Gantikan <br>, <br/>, <br /> dengan karakter newline
                $textWithNewlines = str_replace(['<br>', '<br/>', '</p>', '<br />'], "\n", $text);
                // Hilangkan tag HTML lainnya
                return strip_tags($textWithNewlines);
            }

            // Start Form Data
            
                $Worksheet->Range("D5")->Value = $data->detailHcrf->jobTitle;
                $Worksheet->Range("D6")->Value = $data->detailHcrf->level;
                $Worksheet->Range("D7")->Value = $data->detailHcrf->department;
                $Worksheet->Range("J5")->Value = $data->detailHcrf->neededEmp;
                $Worksheet->Range("J6")->Value = $data->detailHcrf->reportDirectly;
                $Worksheet->Range("J7")->Value = $data->detailHcrf->reportIndirectly;

                $Worksheet->Range("D9")->Value = (($data->detailHcrf->isHeadcount == 1)?'Budgeted':'Replacement');
                $Worksheet->Range("D10")->Value = (($data->detailHcrf->isCriticalPosition == 1)?'Yes':'No');
                $Worksheet->Range("D11")->Value = $data->detailHcrf->expectedDate;

                // Memproses data detail menggunakan fungsi cleanAndConvertHTML
                $jobDescCleaned = cleanAndConvertHTML($data->detailHcrf->jobDesc);
                $trainingPlanCleaned = cleanAndConvertHTML($data->detailHcrf->trainingPlan);
                $careerDevPlanCleaned = cleanAndConvertHTML($data->detailHcrf->careerDevPlan);
                $specialSkillsCleaned = cleanAndConvertHTML($data->detailHcrf->specialSkills);

                $Worksheet->Range("A14")->Value = $jobDescCleaned;

                $Worksheet->Range("D20")->Value = $data->detailHcrf->location;
                $Worksheet->Range("A24")->Value = $trainingPlanCleaned;
                $Worksheet->Range("A28")->Value = $careerDevPlanCleaned;

                $Worksheet->Range("B33")->Value = $data->detailHcrf->education;
                $Worksheet->Range("H33")->Value = $data->detailHcrf->experienceLength;
                $Worksheet->Range("B35")->Value = $data->detailHcrf->language;
                $Worksheet->Range("H35")->Value = $specialSkillsCleaned;


                // Format the text in cell E23
                // $range = $Worksheet->Range("E23");

                // $Worksheet->Rows("14:14")->AutoFit();
                // Mengaktifkan pembungkusan teks dalam sel yang di-merge
                // $range->WrapText = true;

                // Menyesuaikan tinggi semua baris yang bersesuaian (A14:K19)
                // for ($i = 14; $i <= 19; $i++) {
                //     $Worksheet->Rows($i)->AutoFit();
                // }

                // Set "Reason for requisition/purchase:" to red
                // $range->Characters(1, 31)->Font->Color = -16776961; // RGB for red
                // $range->Characters(1, 31)->Font->Underline = true; // underline

                // Set the reason text to black
                // $reasonStart = 32; // Assuming the reason starts immediately after the colon and space
                // $reasonLength = strlen($data->detailHcrf->Reason);
                // $range->Characters($reasonStart, $reasonLength)->Font->Color = 0; // RGB for black

            // End Form Data

            $picpath = public_path("assets/images/approved.png");
            
            function addPictureToWorksheet($Worksheet, $picPath, $row, $column, $height, $excel) {
                $pic = $Worksheet->Shapes->AddPicture($picPath, False, True, 0, 0, -1, -1);
                $pic->Height = $height;
                $pic->Top = $excel->Cells($row, $column)->Top;
                $pic->Left = $excel->Cells($row, $column)->Left;
            }

            // // signature originator
            $Worksheet->Range("D46")->Value = $emp->FullName;
            $Worksheet->Range("D47")->Value = $subimissionDate->format('Y-m-d');
            addPictureToWorksheet($Worksheet, $picpath, 42, 4, 30, $excel);
            
            // // signature approver
            foreach ($dataAppr as $appr) {
                if($appr->sequence == 3) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("F46")->Value = $appr->apprname;
                        $Worksheet->Range("F47")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 42, 6, 30, $excel);
                    }
                }
                if($appr->sequence == 4) {
                    if($appr->approvalAction == 3) {
                        $Worksheet->Range("I46")->Value = $appr->apprname;
                        $Worksheet->Range("I47")->Value = $appr->approvalDate;
                        addPictureToWorksheet($Worksheet, $picpath, 42, 9, 30, $excel);
                    }
                }
            }
            
            $xlTypePDF = 0;
			$xlQualityStandard = 0;

            $code_sanitized = str_replace('/', '_', $data->code->code);
			$fileName = $data->id . '_' . $code_sanitized . '_' . date("Ymd") . '.pdf';
			$fileName =  preg_replace("/[^a-z0-9\_\-\.]/i", '', $fileName);
            $filePath = public_path('template/hris/hcrf/pdf/' . $fileName);
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
			
            $pathfilename = 'public/template/hris/hcrf/pdf/' . $fileName;

            $updateData = $this->model->find($data->id);
			$updateData->approveddoc = str_replace("\\", "/", $pathfilename);
			$updateData->save();

            $this->processcopy($pathfilename);

			return $pathfilename;

		} catch (\Exception $e) {
            // Log error
            $ip = $request->ip();
            $url = $request->url();
            $action = 'gen-pdf-hcrf';
            $this->logerror($ip, $url, $action, $e->getMessage());

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
		}

    }
}
