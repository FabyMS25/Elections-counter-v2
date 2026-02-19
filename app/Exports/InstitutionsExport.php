<?php

namespace App\Exports;

use App\Models\Institution;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InstitutionsExport
{
    public function export()
    {
        try {
            $institutions = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone'
            ])->orderBy('name')->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();            
            $headers = [
                'A1' => 'Código',
                'B1' => 'Nombre',
                'C1' => 'Dirección',
                'D1' => 'Departamento',
                'E1' => 'Provincia',
                'F1' => 'Municipio',
                'G1' => 'Localidad',
                'H1' => 'Distrito',
                'I1' => 'Zona',
                'J1' => 'Ciudadanos Habilitados',
                'K1' => 'Actas Computadas',
                'L1' => 'Actas Anuladas',
                'M1' => 'Actas Habilitadas',
                'N1' => 'Activo',
                'O1' => 'Fecha Creación',
                'P1' => 'Última Actualización'
            ];            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:P1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row = 2;
            foreach ($institutions as $institution) {
                $sheet->setCellValue('A'.$row, $institution->code ?? '');
                $sheet->setCellValue('B'.$row, $institution->name);
                $sheet->setCellValue('C'.$row, $institution->address ?? '');
                $sheet->setCellValue('D'.$row, $institution->locality->municipality->province->department->name ?? '');
                $sheet->setCellValue('E'.$row, $institution->locality->municipality->province->name ?? '');
                $sheet->setCellValue('F'.$row, $institution->locality->municipality->name ?? '');
                $sheet->setCellValue('G'.$row, $institution->locality->name ?? '');
                $sheet->setCellValue('H'.$row, $institution->district->name ?? '');
                $sheet->setCellValue('I'.$row, $institution->zone->name ?? '');
                $sheet->setCellValue('J'.$row, $institution->registered_citizens ?? 0);
                $sheet->setCellValue('K'.$row, $institution->total_computed_records ?? 0);
                $sheet->setCellValue('L'.$row, $institution->total_annulled_records ?? 0);
                $sheet->setCellValue('M'.$row, $institution->total_enabled_records ?? 0);
                $sheet->setCellValue('N'.$row, $institution->active ? 'Sí' : 'No');
                $sheet->setCellValue('O'.$row, $institution->created_at ? $institution->created_at->format('d/m/Y H:i') : '');
                $sheet->setCellValue('P'.$row, $institution->updated_at ? $institution->updated_at->format('d/m/Y H:i') : '');
                $row++;
            }
            foreach (range('A', 'P') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $fileName = 'instituciones_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
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

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();            
            $headers = [
                'A1' => 'Código',
                'B1' => 'Nombre',
                'C1' => 'Dirección',
                'D1' => 'Departamento',
                'E1' => 'Provincia',
                'F1' => 'Municipio',
                'G1' => 'Localidad',
                'H1' => 'Distrito',
                'I1' => 'Zona',
                'J1' => 'Ciudadanos_Habilitados',
                'K1' => 'Actas_Computadas',
                'L1' => 'Actas_Anuladas',
                'M1' => 'Actas_Habilitadas',
                'N1' => 'Activo'
            ];            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:N1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');

            $sampleData = [
                ['INST001', 'Colegio Nacional Simón Bolívar', 'Av. 16 de Julio 1234', 'La Paz', 'Murillo', 'La Paz', 'Centro', 'Distrito 1', 'Zona Norte', '500', '10', '0', '10', 'Sí'],
                ['INST002', 'Unidad Educativa Santa Rosa', 'Calle Comercio 567', 'Cochabamba', 'Cercado', 'Cochabamba', 'Villa Coronilla', '', '', '300', '8', '1', '7', 'Sí'],
                ['', 'Instituto Tecnológico Superior', 'Plaza Principal s/n', 'Santa Cruz', 'Andrés Ibáñez', 'Santa Cruz de la Sierra', 'Plan 3000', 'Distrito 8', 'Zona Este', '750', '15', '0', '15', 'No']
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
            foreach (range('A', 'N') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $instructionRow = $row + 2;
            $sheet->setCellValue('A'.$instructionRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A'.$instructionRow)->getFont()->setBold(true);            
            $instructions = [
                'El código se genera automáticamente si se deja vacío',
                'Los campos Departamento, Provincia, Municipio y Localidad son obligatorios',
                'Distrito y Zona son opcionales',
                'Para el campo Activo use: Sí/No, Si/No, True/False, 1/0',
                'Los campos numéricos pueden dejarse vacíos (se asumirá 0)',
                'Elimine estas filas de ejemplo antes de importar sus datos'
            ];
            foreach ($instructions as $i => $instruction) {
                $sheet->setCellValue('A'.($instructionRow + $i + 1), '• ' . $instruction);
            }
            $fileName = 'plantilla_instituciones.xlsx';
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