<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\EmployeeDocument;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends BaseController
{
    public function __construct(private DocumentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $documents = EmployeeDocument::with('employee.division')
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->doc_type, fn ($q, $v) => $q->where('doc_type', $v))
            ->when($request->status, function ($q, $status) {
                if ($status === 'expired') {
                    $q->where('expired_at', '<', now());
                } elseif ($status === 'expiring_soon') {
                    $q->whereBetween('expired_at', [now(), now()->addDays(90)]);
                }
            })
            ->orderBy('expired_at')
            ->paginate($request->per_page ?? 15);

        return $this->paginated(
            $documents->setCollection(
                $documents->getCollection()->map(fn ($d) => new DocumentResource($d))
            )
        );
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->service->store(
            $request->validated(),
            $request->file('file')
        );

        return $this->success(new DocumentResource($document), 'Dokumen berhasil ditambahkan', 201);
    }

    public function show(EmployeeDocument $document): JsonResponse
    {
        $document->load('employee');
        return $this->success(new DocumentResource($document));
    }

    public function update(Request $request, EmployeeDocument $document): JsonResponse
    {
        $this->authorize('documents.update');

        $data = $request->validate([
            'doc_number' => ['nullable', 'string', 'max:60'],
            'issued_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date'],
            'holder' => ['nullable', 'in:staff,company'],
            'renewal_status' => ['nullable', 'in:none,in_process,done'],
            'notes' => ['nullable', 'string'],
        ]);

        $document = $this->service->update($document, $data, $request->file('file'));

        return $this->success(new DocumentResource($document), 'Dokumen diperbarui');
    }

    public function destroy(EmployeeDocument $document): JsonResponse
    {
        $this->authorize('documents.delete');
        $this->service->delete($document);
        return $this->success(null, 'Dokumen dihapus');
    }

    public function download(EmployeeDocument $document): JsonResponse
    {
        $this->authorize('documents.download');

        $url = $this->service->getDownloadUrl($document);
        if (!$url) {
            return $this->error('File tidak tersedia', 404);
        }

        return $this->success(['url' => $url]);
    }
}
