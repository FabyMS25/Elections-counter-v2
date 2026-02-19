<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function index(){
        try {
            $user = Auth::user();            
            $manager = Manager::with(['votingTable.institution'])
                ->where('user_id', $user->id)
                ->first();
            $institutions = Institution::where('active', true)->get();
            $votingTables = collect();
            
            if ($manager) {
                $selectedInstitution = $manager->votingTable->institution;
                $selectedVotingTable = $manager->votingTable;

                $votingTables = VotingTable::where('institution_id', $selectedInstitution->id)
                    ->where('status', 'active')
                    ->get();
            } else {
                // Admin (or other roles)
                $selectedInstitution = null;
                $selectedVotingTable = null;

                // Show all active institutions
                $institutions = Institution::where('active', true)->get();

                // Leave $votingTables empty until institution is selected
                $votingTables = collect();
                $votes = collect();
            }

            
            // Get all candidates
            $candidates = Candidate::all();
            
            // Get existing votes if a voting table is selected
            $votes = collect();
            if ($selectedVotingTable) {
                $votes = Vote::with(['candidate'])
                    ->where('voting_table_id', $selectedVotingTable->id)
                    ->get();
            }
                
            return view('tables-votes', compact(
                'selectedInstitution', 
                'selectedVotingTable', 
                'candidates', 
                'votes',
                'manager',
                'institutions',
                'votingTables'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error loading votes: ' . $e->getMessage());
            session()->flash('error', 'Error loading votes data.');
            
            return view('tables-votes', [
                'institutions' => collect(),
                'votingTables' => collect(),
                'candidates' => collect(),
                'votes' => collect(),
                'selectedInstitution' => null,
                'selectedVotingTable' => null,
                'manager' => null
            ]);
        }
    }

    public function getVotingTables($institutionId){
        try {
            $votingTables = VotingTable::where('institution_id', $institutionId)
                ->where('status', 'active')
                ->get();
                
            return response()->json([
                'success' => true,
                'votingTables' => $votingTables
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading voting tables: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading voting tables.'
            ], 500);
        }
    }

    public function completeVotation(Request $request){
        try {
            $user = Auth::user();
            $votingTableId = $request->input('voting_table_id');
            $votesData = $request->input('votes', []);
            
            // Verify the user is either manager of this voting table or has selected one
            $isManager = Manager::where('user_id', $user->id)
                ->where('voting_table_id', $votingTableId)
                ->exists();
                
            // If user is not a manager, we still allow them to complete votation
            // but we might want to add additional validation here if needed
            if (!$isManager) {
                // Optional: Add any additional validation for non-managers here
            }
            
            // Check if voting table exists and is active
            $votingTable = VotingTable::find($votingTableId);
            if (!$votingTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voting table not found.'
                ], 404);
            }
            
            if ($votingTable->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This voting table is already closed.'
                ], 422);
            }
            
            $totalVotes = array_sum($votesData);
            
            // Check if voting table capacity is not exceeded
            if ($votingTable->capacity && $totalVotes > $votingTable->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total votes exceed the voting table capacity of ' . $votingTable->capacity . '.'
                ], 422);
            }
            
            // Use transaction to ensure data consistency
            DB::beginTransaction();
            
            try {
                // Save all votes
                foreach ($votesData as $candidateId => $quantity) {
                    if ($quantity > 0) {
                        Vote::updateOrCreate(
                            [
                                'voting_table_id' => $votingTableId,
                                'candidate_id' => $candidateId
                            ],
                            [
                                'quantity' => $quantity,
                                'user_id' => $user->id,
                                'verified_at' => now()
                            ]
                        );
                    }
                }
                
                // Mark the voting table as closed
                $votingTable->status = 'closed';
                $votingTable->save();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Votation completed successfully. ' . $totalVotes . ' votes saved and voting table is now closed.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error completing votation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error completing votation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function manageVotes()
    {
        try {
            $institutions = Institution::where('active', true)->get();
            $candidates = Candidate::all();
            
            // Get all voting tables with their votes
            $votingTables = VotingTable::with(['institution', 'votes.candidate'])
                ->orderBy('institution_id')
                ->orderBy('number')
                ->get();
                
            return view('manage-votes', compact('institutions', 'candidates', 'votingTables'));
            
        } catch (\Exception $e) {
            \Log::error('Error loading vote management: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading vote management data.');
        }
    }

    // Add this method to save votes from the management view
    public function saveVotes(Request $request)
    {
        try {
            $user = Auth::user();
            $votingTableId = $request->input('voting_table_id');
            $votesData = $request->input('votes', []);
            
            // Verify voting table exists
            $votingTable = VotingTable::find($votingTableId);
            if (!$votingTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voting table not found.'
                ], 404);
            }
            
            $totalVotes = array_sum($votesData);
            
            // Check if voting table capacity is not exceeded
            if ($votingTable->capacity && $totalVotes > $votingTable->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total votes exceed the voting table capacity of ' . $votingTable->capacity . '.'
                ], 422);
            }
            
            // Use transaction to ensure data consistency
            DB::beginTransaction();
            
            try {
                // Save all votes
                foreach ($votesData as $candidateId => $quantity) {
                    if ($quantity > 0) {
                        Vote::updateOrCreate(
                            [
                                'voting_table_id' => $votingTableId,
                                'candidate_id' => $candidateId
                            ],
                            [
                                'quantity' => $quantity,
                                'user_id' => $user->id,
                                'verified_at' => now()
                            ]
                        );
                    } else {
                        // Remove votes if quantity is 0
                        Vote::where('voting_table_id', $votingTableId)
                            ->where('candidate_id', $candidateId)
                            ->delete();
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Votes saved successfully. ' . $totalVotes . ' votes registered.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error saving votes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving votes: ' . $e->getMessage()
            ], 500);
        }
    }
    // Other resource methods (create, store, show, edit, update, destroy)
    public function create()
    {
        return redirect()->route('votes.index');
    }
    
    public function store(Request $request)
    {
        return redirect()->route('votes.index');
    }
    
    public function show($id)
    {
        return redirect()->route('votes.index');
    }
    
    public function edit($id)
    {
        return redirect()->route('votes.index');
    }
    
    public function update(Request $request, $id)
    {
        return redirect()->route('votes.index');
    }
    
    public function destroy($id)
    {
        return redirect()->route('votes.index');
    }
}