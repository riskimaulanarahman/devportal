<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Services\DataCandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataCandidateController extends Controller
{
    /** @var DataCandidateService */
    private $service;

    public function __construct(DataCandidateService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        $results = $this->service->getPreview($filters, 10);

        return view('area.data-candidate', [
            'filters' => $filters,
            'results' => $results,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $personal = $this->service->getDetail($id);

        if (!$personal) {
            return response()->json([
                'message' => 'Data kandidat tidak ditemukan.',
            ], 404);
        }

        $html = view('area.partials.data-candidate-detail', [
            'personal' => $personal,
        ])->render();

        return response()->json([
            'html' => $html,
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        $filters = $this->extractFilters($request);

        if (!$this->service->hasResults($filters)) {
            return redirect()
                ->route('data-candidate.index', $this->service->sanitizeFilters($filters))
                ->with('warning', 'Data tidak ditemukan untuk filter yang dipilih.');
        }

        $filename = 'data-candidate-' . now()->format('Ymd_His') . '.csv';
        $headings = $this->service->headings();

        return response()->streamDownload(function () use ($filters, $headings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings);

            $this->service->chunkedExport($filters, function (array $row) use ($handle) {
                fputcsv($handle, $row);
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function extractFilters(Request $request): array
    {
        $keyword = trim((string) $request->input('keyword', ''));

        return [
            'keyword' => $keyword !== '' ? $keyword : null,
        ];
    }
}