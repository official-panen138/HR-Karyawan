<?php

namespace App\Services;

use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function store(array $data, ?object $file = null): EmployeeDocument
    {
        if ($file) {
            $path = $file->store(
                "hr-docs/{$data['employee_id']}/{$data['doc_type']}",
                'r2'
            );
            $data['r2_key'] = $path;
        }

        return EmployeeDocument::create($data);
    }

    public function update(EmployeeDocument $document, array $data, ?object $file = null): EmployeeDocument
    {
        if ($file) {
            if ($document->r2_key) {
                Storage::disk('r2')->delete($document->r2_key);
            }
            $path = $file->store(
                "hr-docs/{$document->employee_id}/{$document->doc_type->value}",
                'r2'
            );
            $data['r2_key'] = $path;
        }

        $document->update($data);
        return $document->fresh();
    }

    public function delete(EmployeeDocument $document): void
    {
        if ($document->r2_key) {
            Storage::disk('r2')->delete($document->r2_key);
        }
        $document->delete();
    }

    public function getDownloadUrl(EmployeeDocument $document): ?string
    {
        if (!$document->r2_key) {
            return null;
        }

        return Storage::disk('r2')->temporaryUrl($document->r2_key, now()->addMinutes(15));
    }
}
