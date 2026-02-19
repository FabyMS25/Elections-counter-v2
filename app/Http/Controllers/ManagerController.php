<?php
namespace App\Http\Controllers;
use App\Models\Manager;
use App\Models\User;
use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\Department;
use App\Models\Municipality;
use App\Models\Locality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ManagerController extends Controller
{
    public function index()
    {
        try {
            $managers = Manager::with(['user', 'votingTable.institution'])->get();            
            $institutions = Institution::all();            
        } catch (\Exception $e) {
            \Log::error('Error loading managers: ' . $e->getMessage());
            $managers = collect();
            $institutions = collect();
            session()->flash('error', 'Error loading managers data.');
        }        
        return view('tables-managers', compact('managers', 'institutions'));
    }

    public function getVotingTables($institutionId)
    {
        try {
            $votingTables = VotingTable::where('institution_id', $institutionId)
                ->where('status', 'activo')
                ->get();
            
            return response()->json($votingTables);
        } catch (\Exception $e) {
            \Log::error('Error fetching voting tables: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'id_card' => 'nullable|string|max:50|unique:managers,id_card',
                'role' => 'required|in:presidente,secretario,escrutador',
                'voting_table_id' => 'required|exists:voting_tables,id',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'institution_id' => 'required|exists:institutions,id',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'id_card.unique' => 'Este número de identificación ya existe.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.unique' => 'Este correo electrónico ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'institution_id.required' => 'Debe seleccionar una institución.',
                'voting_table_id.required' => 'Debe seleccionar una mesa de votación.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            Manager::create([
                'name' => $request->name,
                'id_card' => $request->id_card,
                'role' => $request->role,
                'voting_table_id' => $request->voting_table_id,
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('managers.index')
                            ->with('success', 'El gestor fue creado con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating manager: ' . $e->getMessage());
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al crear el gestor. Por favor intente nuevamente.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $manager = Manager::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'id_card' => 'nullable|string|max:50|unique:managers,id_card,' . $id,
                'role' => 'required|in:presidente,secretario,escrutador',
                'voting_table_id' => 'required|exists:voting_tables,id',
                'email' => 'required|email|unique:users,email,' . $manager->user_id,
                'password' => 'nullable|min:8|confirmed',
                'institution_id' => 'required|exists:institutions,id',
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'id_card.unique' => 'Este número de identificación ya existe.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.unique' => 'Este correo electrónico ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'institution_id.required' => 'Debe seleccionar una institución.',
                'voting_table_id.required' => 'Debe seleccionar una mesa de votación.',
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            $manager->user->update($userData);

            $manager->update([
                'name' => $request->name,
                'id_card' => $request->id_card,
                'role' => $request->role,
                'voting_table_id' => $request->voting_table_id,
            ]);
            
            return redirect()->route('managers.index')
                            ->with('success', 'El gestor fue actualizado con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating manager: ' . $e->getMessage());
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error al actualizar el gestor. Por favor intente nuevamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $manager = Manager::findOrFail($id);
            $user = $manager->user;            
            $manager->delete();
            $user->delete();            
            return redirect()->route('managers.index')
                            ->with('success', 'El gestor fue eliminado correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error deleting manager: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Error al eliminar el gestor. Por favor intente nuevamente.');
        }
    }
}