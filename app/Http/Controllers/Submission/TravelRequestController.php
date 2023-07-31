<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Submission\Travelrequest;

class TravelRequestController extends Controller
{
    private $model;
    private $codename;

    public function __construct()
    {
        $this->model = new Travelrequest();
        $this->codename = 'BT';
    }

    public function index(Request $request)
    {
        try {
            
            // Mengambil nilai id dari request
            $id = $request->id;

            // Mengambil data dari database dengan beberapa join dan kondisi where
            $data = $this->model
                ->select('tbl_tr.*','codes.code','empa.FullName as creatorname','empb.FullName as foremployee')
                ->orderBy('CreatedDate','desc')
                ->leftJoin('codes','tbl_tr.code_id','codes.id')
                ->leftJoin('tbl_employee as empa','tbl_tr.CreatedBy','empa.id')
                ->leftJoin('tbl_employee as empb','tbl_tr.employee_id','empb.id')
                ->where('CreatedBy',$this->getEmployeeID()->id)
                ->get();

            // Mengembalikan data dalam bentuk JSON dengan memberikan status, pesan dan data
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
        try {

            $requestData = $request->all();

            $this->model->create($requestData);

            return response()->json(["status" => "success", "message" => $this->getMessage()['store']]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {

            $data = $this->model->select('tbl_tr.*','codes.code')
            ->leftJoin('codes','tbl_tr.code_id','codes.id')
            ->where('tbl_tr.id',$id)
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->codename);
                $data->save();
            }

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            // Mengambil semua data dari request
            $requestData = $request->all();

            // Mengatur nilai variabel berdasarkan nilai dari request
            if($request->TravelUse == 0) {
                $requestData['isLandTransport'] = 1;
                $requestData['isAirTransport'] = 0;
                $requestData['viaAir'] = ($request->TravelUse) ? null : $request->viaAir;
            } else if($request->TravelUse == 1) {
                $requestData['isLandTransport'] = 0;
                $requestData['isAirTransport'] = 1;
                $requestData['viaLand'] = ($request->TravelUse) ? null : $request->viaLand;
            }

            // Mengatur nilai variabel berdasarkan nilai dari request
            if($request->jobfinishdate) {
                $requestData['OtherLandTransportDesc'] = '';
            } else if($request->OtherLandTransportDesc) {
                $requestData['jobfinishdate'] = null;
            } else {
                $requestData['OtherLandTransportDesc'] = '';
                $requestData['jobfinishdate'] = null;
            }

            // Mencari data berdasarkan id dan mengupdate data dengan nilai dari $requestData
            $data = $this->model->findOrFail($id);
            $data->update($requestData);

            // Mengembalikan data dalam bentuk JSON dengan memberikan status, pesan dan data
            return response()->json([
                'status' => "success",
                'message' => $this->getMessage()['update']
            ]);

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
}
