<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\District;
use App\Models\Zone;
use App\Models\VotingTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\InstitutionsExport;
use App\Imports\InstitutionsImport;

class InstitutionController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function index()
    {
        try {
            $institutions = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone',
                'votingTables'
            ])
            ->orderBy('name')
            ->paginate(self::ITEMS_PER_PAGE);
            $departments = Department::orderBy('name')->get();
            $provinces = Province::orderBy('name')->get();
            $municipalities = Municipality::orderBy('name')->get();
            $localities = Locality::orderBy('name')->get();
            $districts = District::orderBy('name')->get();
            $zones = Zone::orderBy('name')->get();

        } catch (\Exception $e) {
            Log::error('Error loading institutions: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $institutions = collect();
            $departments = collect();
            $provinces = collect();
            $municipalities = collect();
            $localities = collect();
            $districts = collect();
            $zones = collect();            
            return redirect()->back()->with('error', 'Error loading institutions data.');
        }   
        return view('tables-institutions', compact(
            'institutions', 'departments', 'provinces',
            'municipalities', 'localities', 'districts', 'zones'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:institutions,name',
                'code' => 'nullable|string|max:50|unique:institutions,code',
                'address' => 'nullable|string|max:500',
                'locality_id' => 'required|exists:localities,id',
                'district_id' => 'nullable|exists:districts,id',
                'zone_id' => 'nullable|exists:zones,id',
                'registered_citizens' => 'nullable|integer|min:0',
                'total_computed_records' => 'nullable|integer|min:0',
                'total_annulled_records' => 'nullable|integer|min:0',
                'total_enabled_records' => 'nullable|integer|min:0',
                'active' => 'nullable|boolean',
            ]);
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name']);
            }
            $validated['active'] = $validated['active'] ?? true;            
            DB::transaction(function () use ($validated) {
                Institution::create($validated);
            });            
            return redirect()->route('institutions.index')
                            ->with('success', 'La institución fue creada con éxito.');
                            
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating institution: ' . $e->getMessage(), [
                'data' => $request->except('_token'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear la institución. Por favor intente nuevamente.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $institution = Institution::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:institutions,name,' . $id,
                'code' => 'nullable|string|max:50|unique:institutions,code,' . $id,
                'address' => 'nullable|string|max:500',
                'locality_id' => 'required|exists:localities,id',
                'district_id' => 'nullable|exists:districts,id',
                'zone_id' => 'nullable|exists:zones,id',
                'registered_citizens' => 'nullable|integer|min:0',
                'total_computed_records' => 'nullable|integer|min:0',
                'total_annulled_records' => 'nullable|integer|min:0',
                'total_enabled_records' => 'nullable|integer|min:0',
                'active' => 'nullable|boolean',
            ]);
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateInstitutionCode($validated['name'], $id);
            }
            $validated['active'] = $validated['active'] ?? false;            
            DB::transaction(function () use ($institution, $validated) {
                $institution->update($validated);
            });            
            return redirect()->route('institutions.index')
                            ->with('success', 'La institución fue actualizada con éxito.');                            
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating institution: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->except('_token'),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al actualizar la institución. Por favor intente nuevamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $institution = Institution::withCount('votingTables')->findOrFail($id);
            if ($institution->voting_tables_count > 0) {
                return redirect()->back()
                                ->with('error', 'No se puede eliminar la institución porque tiene mesas de votación asociadas. Elimine primero las mesas de votación.');
            }            
            DB::transaction(function () use ($institution) {
                $institution->delete();
            });            
            return redirect()->route('institutions.index')
                            ->with('success', 'La institución fue eliminada correctamente.');                            
        } catch (\Exception $e) {
            Log::error('Error deleting institution: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);            
            return redirect()->back()
                            ->with('error', 'Error al eliminar la institución. Por favor intente nuevamente.');
        }
    }
    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:institutions,id'
            ]);
            $ids = $request->input('ids');
            $count = count($ids);
            $institutionsWithTables = Institution::whereIn('id', $ids)
                ->whereHas('votingTables')
                ->count();
            if ($institutionsWithTables > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar instituciones que tienen mesas de votación asociadas.'
                ], 422);
            }
            $deleted = Institution::whereIn('id', $ids)->delete();
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} instituciones correctamente.",
                    'deleted_count' => $deleted
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'No se pudieron eliminar las instituciones.'
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);            
        } catch (\Exception $e) {
            \Log::error('Error deleting multiple institutions: ' . $e->getMessage());            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al eliminar las instituciones.'
            ], 500);
        }
    }
    public function export()
    {
        try {
            $export = new InstitutionsExport();
            $filePath = $export->export();            
            return response()->download(storage_path("app/{$filePath}"))
                            ->deleteFileAfterSend(true);            
        } catch (\Exception $e) {
            Log::error('Error exporting institutions: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->back()->with('error', 'Error al exportar las instituciones: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ]);
            $import = new InstitutionsImport();
            $result = $import->import($request->file('file'));
            if (!$result['success']) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'Error durante la importación.');
            }
            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('error', 'No se pudo importar ninguna institución.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('institutions.index')
                                ->with('import_errors', $result['errors'])
                                ->with('warning', "Se importaron {$result['success_count']} instituciones. Algunas filas tuvieron errores.");
            }
            return redirect()->route('institutions.index')
                            ->with('success', "Se importaron {$result['success_count']} instituciones correctamente.");
        } catch (\Exception $e) {
            Log::error('Error importing institutions: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->route('institutions.index')
                            ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new InstitutionsExport();
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

    public function getProvinces(Department $department)
    {
        try {
            $provinces = $department->provinces()->select('id', 'name')->orderBy('name')->get();
            return response()->json($provinces);
        } catch (\Exception $e) {
            Log::error('Error getting provinces: ' . $e->getMessage(), [
                'department_id' => $department->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return response()->json(['error' => 'Error loading provinces'], 500);
        }
    }

    public function getMunicipalities(Province $province)
    {
        try {
            $municipalities = $province->municipalities()->select('id', 'name')->orderBy('name')->get();
            return response()->json($municipalities);
        } catch (\Exception $e) {
            Log::error('Error getting municipalities: ' . $e->getMessage(), [
                'province_id' => $province->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json(['error' => 'Error loading municipalities'], 500);
        }
    }

    public function getLocalities(Municipality $municipality)
    {
        try {
            $localities = $municipality->localities()->select('id', 'name')->orderBy('name')->get();
            return response()->json($localities);
        } catch (\Exception $e) {
            Log::error('Error getting localities: ' . $e->getMessage(), [
                'municipality_id' => $municipality->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json(['error' => 'Error loading localities'], 500);
        }
    }

    public function getDistricts(Locality $locality)
    {
        try {
            $districts = District::where('municipality_id', $locality->municipality_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();                
            return response()->json($districts);
        } catch (\Exception $e) {
            Log::error('Error getting districts: ' . $e->getMessage(), [
                'locality_id' => $locality->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return response()->json(['error' => 'Error loading districts'], 500);
        }
    }    
    public function getZones(District $district)
    {
        try {
            $zones = $district->zones()->select('id', 'name')->orderBy('name')->get();
            return response()->json($zones);
        } catch (\Exception $e) {
            Log::error('Error getting zones: ' . $e->getMessage(), [
                'district_id' => $district->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return response()->json(['error' => 'Error loading zones'], 500);
        }
    }
    private function generateInstitutionCode($name, $excludeId = null)
    {
        $baseCode = 'INST' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $counter = 1;
        $code = $baseCode . sprintf('%03d', $counter);
        $query = Institution::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $counter++;
            $code = $baseCode . sprintf('%03d', $counter);
            $query = Institution::where('code', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        return $code;
    }
    public function show($id)
    {
        try {
            $institution = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone',
                'votingTables' => function($query) {
                    $query->orderBy('number');
                }
            ])->findOrFail($id);
            return view('institutions.show', compact('institution'));            
        } catch (\Exception $e) {
            Log::error('Error showing institution: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return redirect()->route('institutions.index')
                            ->with('error', 'Error al cargar los detalles de la institución.');
        }
    }
    public function getByLocality($localityId)
    {
        try {
            $institutions = Institution::where('locality_id', $localityId)
                ->where('active', true)
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();                
            return response()->json($institutions);
        } catch (\Exception $e) {
            Log::error('Error getting institutions by locality: ' . $e->getMessage(), [
                'locality_id' => $localityId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);            
            return response()->json(['error' => 'Error loading institutions'], 500);
        }
    }
}