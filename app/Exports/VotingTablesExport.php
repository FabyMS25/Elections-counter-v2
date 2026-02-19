<?php

namespace App\Exports;

use App\Models\VotingTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VotingTablesExport
{
    public function export()
    {
        try {
            $votingTables = VotingTable::with(['institution'])->orderBy('number')->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();            
            $headers = [
                'A1' => 'Código',
                'B1' => 'Número',
                'C1' => 'Desde Nombre',
                'D1' => 'Hasta Nombre',
                'E1' => 'Ciudadanos Registrados',
                'F1' => 'Papeletas Computadas',
                'G1' => 'Papeletas Anuladas',
                'H1' => 'Papeletas Habilitadas',
                'I1' => 'Estado',
                'J1' => 'Institución',
                'K1' => 'Código Institución',
                'L1' => 'Fecha Creación',
                'M1' => 'Última Actualización'
            ];            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:M1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row = 2;
            foreach ($votingTables as $votingTable) {
                $sheet->setCellValue('A'.$row, $votingTable->code);
                $sheet->setCellValue('B'.$row, $votingTable->number);
                $sheet->setCellValue('C'.$row, $votingTable->from_name ?? '');
                $sheet->setCellValue('D'.$row, $votingTable->to_name ?? '');
                $sheet->setCellValue('E'.$row, $votingTable->registered_citizens ?? 0);
                $sheet->setCellValue('F'.$row, $votingTable->computed_records ?? 0);
                $sheet->setCellValue('G'.$row, $votingTable->annulled_records ?? 0);
                $sheet->setCellValue('H'.$row, $votingTable->enabled_records ?? 0);
                $sheet->setCellValue('I'.$row, $this->getStatusText($votingTable->status));
                $sheet->setCellValue('J'.$row, $votingTable->institution->name ?? '');
                $sheet->setCellValue('K'.$row, $votingTable->institution->code ?? '');
                $sheet->setCellValue('L'.$row, $votingTable->created_at ? $votingTable->created_at->format('d/m/Y H:i') : '');
                $sheet->setCellValue('M'.$row, $votingTable->updated_at ? $votingTable->updated_at->format('d/m/Y H:i') : '');
                $row++;
            }
            foreach (range('A', 'N') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $fileName = 'mesas_votacion_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = "exports/{$fileName}";            
            Storage::makeDirectory('exports');            
            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path("app/{$filePath}"));            
            return $filePath;
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            throw new \Exception('Error al generar el archivo de exportación: ' . $e->getMessage());
        }
    }

    private function getStatusText($status)
    {
        $statuses = [
            'activo' => 'Activo',
            'cerrado' => 'Cerrado',
            'pendiente' => 'Pendiente'
        ];
        return $statuses[$status] ?? $status;
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();            
            $headers = [
                'A1' => 'Código',
                'B1' => 'Número',
                'C1' => 'Desde_Nombre',
                'D1' => 'Hasta_Nombre',
                'E1' => 'Ciudadanos_Registrados',
                'F1' => 'Papeletas_Computadas',
                'G1' => 'Papeletas_Anuladas',
                'H1' => 'Papeletas_Habilitadas',
                'I1' => 'Estado',
                'J1' => 'Institución',
                'K1' => 'Código_Institución'
            ];            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:K1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');

            $sampleData = [
                ['MESA001', '1', 'Juan Pérez', 'María López', '180', '10', '0', '10', 'activo', 'Colegio Nacional Simón Bolívar', 'INST001'],
                ['MESA002', '2', 'Carlos Rodríguez', 'Ana Martínez', '190', '12', '1', '11', 'activo', 'Colegio Nacional Simón Bolívar', 'INST001'],
                ['MESA003', '1', 'Pedro Gómez', 'Lucía Fernández', '140', '8', '0', '8', 'pendiente', 'Unidad Educativa Santa Rosa', 'INST002']
            ];
            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col.$row, $value);
                    $col++;
                }
                $row++;
            }
            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $instructionRow = $row + 2;
            $sheet->setCellValue('A'.$instructionRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A'.$instructionRow)->getFont()->setBold(true);
            $instructions = [
                'El código debe ser único',
                'El número debe ser único dentro de la misma institución',
                'Para el campo Estado use: activo, cerrado, pendiente',
                'Los campos numéricos pueden dejarse vacíos (se asumirá 0)',
                'La institución puede especificarse por nombre o código',
                'Elimine estas filas de ejemplo antes de importar sus datos'
            ];
            foreach ($instructions as $i => $instruction) {
                $sheet->setCellValue('A'.($instructionRow + $i + 1), '• ' . $instruction);
            }
            $fileName = 'plantilla_mesas_votacion.xlsx';
            $filePath = "templates/{$fileName}";            
            Storage::makeDirectory('templates');            
            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path("app/{$filePath}"));
            return $filePath;
        } catch (\Exception $e) {
            Log::error('Template generation error: ' . $e->getMessage());
            throw new \Exception('Error al generar la plantilla: ' . $e->getMessage());
        }
    }
}