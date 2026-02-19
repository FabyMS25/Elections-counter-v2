<?php

namespace App\Imports;

use App\Models\VotingTable;
use App\Models\Institution;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VotingTablesImport
{
    private $errors = [];
    private $successCount = 0;

    public function import($uploadedFile)
    {
        try {
            $filePath = $uploadedFile->store('imports');
            $spreadsheet = IOFactory::load(storage_path("app/{$filePath}"));
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('El archivo está vacío o no tiene datos válidos.');
            }
            DB::beginTransaction();
            foreach (array_slice($rows, 1) as $index => $row) {
                try {
                    $this->processRow($row, $index + 2);
                } catch (\Exception $e) {
                    $this->errors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            DB::commit();
            Storage::delete($filePath);
            return [
                'success' => true,
                'errors' => $this->errors,
                'success_count' => $this->successCount
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            Log::error('Import error: ' . $e->getMessage());            
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'success_count' => 0
            ];
        }
    }

    private function processRow($row, $rowNumber)
    {
        if (empty($row[0])) {
            throw new \Exception("El código es requerido");
        }
        if (empty($row[1]) || !is_numeric($row[1])) {
            throw new \Exception("El número es requerido y debe ser numérico");
        }
        if (empty($row[8])) {
            throw new \Exception("El estado es requerido");
        }
        if (empty($row[9]) && empty($row[10])) {
            throw new \Exception("Debe especificar una institución por nombre o código");
        }
        $institution = null;
        if (!empty($row[9])) {
            $institution = Institution::where('name', trim($row[9]))->first();
        }
        if (!$institution && !empty($row[10])) {
            $institution = Institution::where('code', trim($row[10]))->first();
        }
        if (!$institution) {
            throw new \Exception("Institución no encontrada: " . (trim($row[10]) ?? '') . " / " . (trim($row[10]) ?? ''));
        }
        $status = strtolower(trim($row[8]));
        if (!in_array($status, ['activo', 'cerrado', 'pendiente'])) {
            throw new \Exception("Estado no válido: " . $status . ". Use: activo, cerrado, pendiente");
        }
        $existingTable = VotingTable::where('institution_id', $institution->id)
                                    ->where('number', intval($row[1]))
                                    ->first();
        if ($existingTable) {
            throw new \Exception("Ya existe una mesa con el número " . $row[1] . " en esta institución");
        }
        VotingTable::updateOrCreate(
            [
                'code' => trim($row[0])
            ],
            [
                'number' => intval($row[1]),
                'from_name' => !empty($row[2]) ? trim($row[2]) : null,
                'to_name' => !empty($row[3]) ? trim($row[3]) : null,
                'registered_citizens' => !empty($row[4]) ? intval($row[4]) : 0,
                'computed_records' => !empty($row[5]) ? intval($row[5]) : 0,
                'annulled_records' => !empty($row[6]) ? intval($row[6]) : 0,
                'enabled_records' => !empty($row[7]) ? intval($row[7]) : 0,
                'status' => $status,
                'institution_id' => $institution->id,
            ]
        );
        $this->successCount++;
    }
}