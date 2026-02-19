<?php

namespace App\Http\Controllers;

use App\Models\VotingTable;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\VotingTablesExport;
use App\Imports\VotingTablesImport;

class VotingTableController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function index()
    {
        try {
            $votingTables = VotingTable::with(['institution'])
                ->orderBy('number')
                ->paginate(self::ITEMS_PER_PAGE);
            $institutions = Institution::where('active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Error loading voting tables: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $votingTables = collect();
            $institutions = collect();
            return redirect()->back()->with('error', 'Error loading voting tables data.');
        }
        
        return view('tables-voting-tables', compact('votingTables', 'institutions'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'nullable|string|max:50',
                'number' => 'required|integer|min:1',
                'from_name' => 'nullable|string|max:255',
                'to_name' => 'nullable|string|max:255',
                'registered_citizens' => 'nullable|integer|min:0',
                'computed_records' => 'nullable|integer|min:0',
                'annulled_records' => 'nullable|integer|min:0',
                'enabled_records' => 'nullable|integer|min:0',
                'status' => 'required|in:activo,cerrado,pendiente',
                'institution_id' => 'required|exists:institutions,id',
            ], [
                'number.required' => 'El número de mesa es obligatorio.',
                'number.integer' => 'El número de mesa debe ser un valor numérico.',
                'number.min' => 'El número de mesa debe ser al menos 1.',
                'status.required' => 'El estado es obligatorio.',
                'status.in' => 'El estado seleccionado no es válido.',
                'institution_id.required' => 'Debe seleccionar una institución.',
                'institution_id.exists' => 'La institución seleccionada no es válida.',
            ]);
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->first();

            if ($existingTable) {
                throw ValidationException::withMessages([
                    'number' => 'Ya existe una mesa con este número en la institución seleccionada.',
                ]);
            }
            if (empty($validated['code'])) {
                $validated['code'] = 'MESA-' . $validated['number'];
            }
            $existingCode = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('code', $validated['code'])
                ->first();
            if ($existingCode) {
                throw ValidationException::withMessages([
                    'code' => 'El código ya existe en esta institución.',
                ]);
            }
            DB::transaction(function () use ($validated) {
                VotingTable::create($validated);
            });
            return redirect()->route('voting-tables.index')
                ->with('success', 'La mesa de votación fue creada con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating voting table: ' . $e->getMessage(), [
                'data' => $request->except('_token'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $validated = $request->validate([
                'code' => 'nullable|string|max:50',
                'number' => 'required|integer|min:1',
                'from_name' => 'nullable|string|max:255',
                'to_name' => 'nullable|string|max:255',
                'registered_citizens' => 'nullable|integer|min:0',
                'computed_records' => 'nullable|integer|min:0',
                'annulled_records' => 'nullable|integer|min:0',
                'enabled_records' => 'nullable|integer|min:0',
                'status' => 'required|in:activo,cerrado,pendiente',
                'institution_id' => 'required|exists:institutions,id',
            ], [
                'number.required' => 'El número de mesa es obligatorio.',
                'number.integer' => 'El número de mesa debe ser un valor numérico.',
                'number.min' => 'El número de mesa debe ser al menos 1.',
                'status.required' => 'El estado es obligatorio.',
                'status.in' => 'El estado seleccionado no es válido.',
                'institution_id.required' => 'Debe seleccionar una institución.',
                'institution_id.exists' => 'La institución seleccionada no es válida.',
            ]);
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingTable) {
                throw ValidationException::withMessages([
                    'number' => 'Ya existe una mesa con este número en la institución seleccionada.',
                ]);
            }
            if (empty($validated['code'])) {
                $validated['code'] = 'MESA-' . $validated['number'];
            }
            $existingCode = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('code', $validated['code'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingCode) {
                throw ValidationException::withMessages([
                    'code' => 'El código ya existe en esta institución.',
                ]);
            }
            DB::transaction(function () use ($votingTable, $validated) {
                $votingTable->update($validated);
            });
            return redirect()->route('voting-tables.index')
                ->with('success', 'La mesa de votación fue actualizada con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating voting table: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->except('_token'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);            
            DB::transaction(function () use ($votingTable) {
                $votingTable->delete();
            });            
            return redirect()->route('voting-tables.index')
                            ->with('success', 'La mesa de votación fue eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting voting table: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                            ->with('error', 'Error al eliminar la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:voting_tables,id'
            ]);            
            $ids = $request->input('ids');
            $count = count($ids);            
            $deleted = VotingTable::whereIn('id', $ids)->delete();            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} mesas de votación correctamente.",
                    'deleted_count' => $deleted
                ]);
            }            
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron eliminar las mesas de votación.'
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);            
        } catch (\Exception $e) {
            Log::error('Error deleting multiple voting tables: ' . $e->getMessage());            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al eliminar las mesas de votación.'
            ], 500);
        }
    }

    public function export()
    {
        try {
            $export = new VotingTablesExport();
            $filePath = $export->export();            
            return response()->download(storage_path("app/{$filePath}"))
                            ->deleteFileAfterSend(true);            
        } catch (\Exception $e) {
            Log::error('Error exporting voting tables: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->back()->with('error', 'Error al exportar las mesas de votación: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ]);
            
            $import = new VotingTablesImport();
            $result = $import->import($request->file('file'));
            
            if (!$result['success']) {
                return redirect()->route('voting-tables.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'Error durante la importación.');
            }
            
            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('voting-tables.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'No se pudo importar ninguna mesa de votación.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('voting-tables.index')
                                ->with('import_errors', $result['errors'])
                                ->with('warning', "Se importaron {$result['success_count']} mesas de votación. Algunas filas tuvieron errores.");
            }
            
            return redirect()->route('voting-tables.index')
                            ->with('success', "Se importaron {$result['success_count']} mesas de votación correctamente.");
        } catch (\Exception $e) {
            Log::error('Error importing voting tables: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->route('voting-tables.index')
                            ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new VotingTablesExport();
            $filePath = $export->downloadTemplate();            
            return response()->download(storage_path("app/{$filePath}"))
                            ->deleteFileAfterSend(true);            
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->back()->with('error', 'Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $votingTable = VotingTable::with(['institution'])->findOrFail($id);
            return view('voting-tables.show', compact('votingTable'));            
        } catch (\Exception $e) {
            Log::error('Error showing voting table: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->route('voting-tables.index')
                            ->with('error', 'Error al cargar los detalles de la mesa de votación.');
        }
    }

    public function getByInstitution($institutionId)
    {
        try {
            $votingTables = VotingTable::where('institution_id', $institutionId)
                ->select('id', 'number', 'code')
                ->orderBy('number')
                ->get();                
            return response()->json($votingTables);
        } catch (\Exception $e) {
            Log::error('Error getting voting tables by institution: ' . $e->getMessage(), [
                'institution_id' => $institutionId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return response()->json(['error' => 'Error loading voting tables'], 500);
        }
    }
}