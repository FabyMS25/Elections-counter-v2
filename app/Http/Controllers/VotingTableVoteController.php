<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VotingTableVoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $totalCount = 0;
            $totalRegisteredCitizens = 0;
            $institutionId = $request->input('institution_id');
            $electionTypeId = $request->input('election_type_id', 1); // Default to first election type
            
            // Get active institutions and election types
            $institutions = Institution::where('active', true)->get();
            $electionTypes = ElectionType::where('active', true)->get();
            $currentElectionType = ElectionType::find($electionTypeId) ?? $electionTypes->first();
            
            // Build voting tables query with proper relationships
            $votingTablesQuery = VotingTable::with([
                'institution', 
                'votes' => function($query) use ($electionTypeId) {
                    $query->where('election_type_id', $electionTypeId);
                },
                'votes.candidate'
            ]);
            
            if ($institutionId) {
                $votingTablesQuery->where('institution_id', $institutionId);
            }
            
            $votingTables = $votingTablesQuery->get();
            
            // Get candidates for the selected election type only
            $candidates = Candidate::where('election_type_id', $electionTypeId)
                                 ->where('active', true)
                                 ->orderBy('name')
                                 ->get();
            
            $candidateTotals = [];
            
            // Initialize candidate totals
            foreach ($candidates as $candidate) {
                $candidateTotals[$candidate->id] = 0;
            }
            
            // Calculate totals per table and overall
            foreach ($votingTables as $table) {
                $totalRegisteredCitizens += $table->registered_citizens ?? 0;
                
                foreach ($table->votes as $vote) {
                    // Only count votes for the current election type
                    if ($vote->election_type_id == $electionTypeId && isset($candidateTotals[$vote->candidate_id])) {
                        $candidateTotals[$vote->candidate_id] += $vote->quantity;
                        $totalCount += $vote->quantity;
                    }
                }
            }
            
            // Calculate candidate statistics
            $candidateStats = [];
            $sortedCandidates = collect($candidateTotals)->sortDesc();
            $maxVotes = $sortedCandidates->first() ?? 0;
            
            foreach ($candidates as $candidate) {
                $votes = $candidateTotals[$candidate->id] ?? 0;
                $percentage = $totalCount > 0 ? ($votes / $totalCount) * 100 : 0;
                $trend = 'neutral';
                
                if ($maxVotes > 0 && $votes == $maxVotes) {
                    $trend = 'up';
                } elseif ($totalCount > 0 && $votes > 0) {
                    $trend = 'down';
                }
                
                // Calculate rank
                $rank = 1;
                foreach ($sortedCandidates as $candidateId => $candidateVotes) {
                    if ($candidateId == $candidate->id) {
                        break;
                    }
                    if ($candidateVotes > $votes) {
                        $rank++;
                    }
                }
                
                $candidateStats[$candidate->id] = [
                    'votes' => $votes,
                    'percentage' => $percentage,
                    'trend' => $trend,
                    'rank' => $rank
                ];
            }
            
            return view('voting-table-votes', compact(
                'totalCount',
                'totalRegisteredCitizens',
                'votingTables',
                'candidates',
                'institutions',
                'electionTypes',
                'currentElectionType',
                'institutionId',
                'electionTypeId',
                'candidateTotals',
                'candidateStats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading voting table votes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->with('error', 'Error loading voting table data.');
        }
    }

    public function registerVotes(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'voting_table_id' => 'required|integer|exists:voting_tables,id',
                'election_type_id' => 'required|integer|exists:election_types,id',
                'votes' => 'required|array',
                'votes.*' => 'integer|min:0',
                'close' => 'boolean'
            ]);

            $user = Auth::user();
            $votingTableId = $validated['voting_table_id'];
            $electionTypeId = $validated['election_type_id'];
            $votesData = $validated['votes'];
            $closeTable = $validated['close'] ?? false;
            
            $votingTable = VotingTable::find($votingTableId);
            if (!$votingTable) {
                return response()->json(['success' => false, 'message' => 'Voting table not found.'], 404);
            }
            
            // Check if table is already closed
            if ($votingTable->status === 'cerrado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify votes for a closed table.'
                ], 422);
            }
            
            // Validate that all candidates belong to the specified election type
            $validCandidateIds = Candidate::where('election_type_id', $electionTypeId)
                                        ->where('active', true)
                                        ->pluck('id')
                                        ->toArray();
            
            foreach (array_keys($votesData) as $candidateId) {
                if (!in_array($candidateId, $validCandidateIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid candidate ID: {$candidateId} for this election type."
                    ], 422);
                }
            }
            
            $totalVotes = array_sum($votesData);
            if ($votingTable->registered_citizens && $totalVotes > $votingTable->registered_citizens) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total votes (' . $totalVotes . ') exceed the registered citizens count of ' . $votingTable->registered_citizens
                ], 422);
            }
            
            DB::transaction(function () use ($votesData, $votingTable, $user, $closeTable, $electionTypeId) {
                foreach ($votesData as $candidateId => $quantity) {
                    $candidate = Candidate::find($candidateId);
                    if (!$candidate || $candidate->election_type_id != $electionTypeId) {
                        Log::warning("Invalid candidate: {$candidateId} for election type: {$electionTypeId}");
                        continue;
                    }
                    
                    if ($quantity > 0) {
                        Vote::updateOrCreate(
                            [
                                'voting_table_id' => $votingTable->id,
                                'candidate_id' => $candidateId,
                                'election_type_id' => $electionTypeId
                            ],
                            [
                                'quantity' => $quantity,
                                'user_id' => $user->id,
                                'verified_at' => now()
                            ]
                        );
                    } else {
                        // Remove vote record if quantity is 0
                        Vote::where('voting_table_id', $votingTable->id)
                            ->where('candidate_id', $candidateId)
                            ->where('election_type_id', $electionTypeId)
                            ->delete();
                    }
                }
                
                // Update computed records count for this election type
                $votingTable->computed_records = $votingTable->votes()
                    ->where('election_type_id', $electionTypeId)
                    ->where('quantity', '>', 0)
                    ->count();
                
                // Update status
                if ($votingTable->status === 'pendiente') {
                    $votingTable->status = 'activo';
                }
                
                if ($closeTable) {
                    $votingTable->status = 'cerrado';
                }
                
                $votingTable->save();
                $this->updateInstitutionTotals($votingTable->institution_id, $electionTypeId);
            });

            return response()->json(['success' => true, 'message' => 'Votes registered successfully.']);
            
        } catch (ValidationException $e) {
            Log::error('Validation error in registerVotes: ' . json_encode($e->errors()));
            return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 422);
        } catch (\Exception $e) {
            Log::error('Error registering votes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while registering votes.'], 500);
        }
    }

    public function registerAllVotes(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'election_type_id' => 'required|integer|exists:election_types,id',
                'tables' => 'required|array',
                'tables.*' => 'required|array',
                'tables.*.*' => 'integer|min:0',
                'close_all' => 'boolean'
            ]);

            $user = Auth::user();
            $electionTypeId = $validated['election_type_id'];
            $tablesData = $validated['tables'];
            $closeAll = $validated['close_all'] ?? false;
            $errors = [];
            $processedTables = 0;

            // Validate candidate IDs for this election type
            $validCandidateIds = Candidate::where('election_type_id', $electionTypeId)
                                        ->where('active', true)
                                        ->pluck('id')
                                        ->toArray();

            DB::transaction(function () use ($tablesData, $user, $closeAll, $electionTypeId, $validCandidateIds, &$errors, &$processedTables) {
                foreach ($tablesData as $tableId => $votesData) {
                    try {
                        // Validate table ID is numeric
                        if (!is_numeric($tableId)) {
                            $errors[] = "Invalid table ID: {$tableId}";
                            continue;
                        }

                        $votingTable = VotingTable::find($tableId);
                        if (!$votingTable) {
                            $errors[] = "Table {$tableId} not found";
                            continue;
                        }

                        // Skip if table is already closed and we're not explicitly closing all
                        if ($votingTable->status === 'cerrado' && !$closeAll) {
                            continue;
                        }
                        
                        // Validate candidate IDs
                        foreach (array_keys($votesData) as $candidateId) {
                            if (!in_array($candidateId, $validCandidateIds)) {
                                $errors[] = "Table {$votingTable->code}: Invalid candidate ID {$candidateId}";
                                continue 2; // Skip this table
                            }
                        }
                        
                        $totalVotes = array_sum($votesData);
                        if ($votingTable->registered_citizens && $totalVotes > $votingTable->registered_citizens) {
                            $errors[] = "Table {$votingTable->code} exceeds registered citizens ({$totalVotes}/{$votingTable->registered_citizens})";
                            continue;
                        }
                        
                        foreach ($votesData as $candidateId => $quantity) {
                            $candidate = Candidate::find($candidateId);
                            if (!$candidate) {
                                Log::warning("Candidate not found: {$candidateId}");
                                continue;
                            }
                            
                            if ($candidate->election_type_id != $electionTypeId) {
                                Log::warning("Candidate {$candidateId} belongs to election type {$candidate->election_type_id}, expected {$electionTypeId}");
                                continue;
                            }
                            
                            if ($quantity > 0) {
                                Vote::updateOrCreate(
                                    [
                                        'voting_table_id' => $votingTable->id,
                                        'candidate_id' => $candidateId,
                                        'election_type_id' => $candidate->election_type_id
                                    ],
                                    [
                                        'quantity' => $quantity,
                                        'user_id' => $user->id,
                                        'verified_at' => now(),
                                        'election_type_id' => $candidate->election_type_id
                                    ]
                                );
                            } else {
                                Vote::where('voting_table_id', $votingTable->id)
                                    ->where('candidate_id', $candidateId)
                                    ->where('election_type_id', $candidate->election_type_id)
                                    ->delete();
                            }
                        }
                        
                        // Update computed records count
                        $votingTable->computed_records = $votingTable->votes()
                            ->where('election_type_id', $electionTypeId)
                            ->where('quantity', '>', 0)
                            ->count();
                        
                        if ($votingTable->status === 'pendiente') {
                            $votingTable->status = 'activo';
                        }
                        
                        if ($closeAll && $votingTable->status !== 'cerrado') {
                            $votingTable->status = 'cerrado';
                        }
                        
                        $votingTable->save();
                        $this->updateInstitutionTotals($votingTable->institution_id, $electionTypeId);
                        $processedTables++;
                        
                    } catch (\Exception $e) {
                        Log::error("Error processing table {$tableId}: " . $e->getMessage());
                        $errors[] = "Error processing table {$tableId}: " . $e->getMessage();
                    }
                }
            });

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some tables had errors: ' . implode(', ', $errors),
                    'processed' => $processedTables
                ], 422);
            }

            if ($processedTables === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tables were processed. All tables may already be closed.'
                ], 422);
            }

            $message = $closeAll
                ? "All votes registered and {$processedTables} tables closed successfully."
                : "All votes registered successfully for {$processedTables} tables.";
                
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (ValidationException $e) {
            Log::error('Validation error in registerAllVotes: ' . json_encode($e->errors()));
            return response()->json(['success' => false, 'message' => 'Invalid data structure provided.'], 422);
        } catch (\Exception $e) {
            Log::error('Error registering all votes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while processing votes.'], 500);
        }
    }

    /**
     * Update institution totals based on voting tables for a specific election type
     */
    private function updateInstitutionTotals($institutionId, $electionTypeId = null)
    {
        try {
            $institution = Institution::find($institutionId);
            if (!$institution) {
                Log::warning("Institution not found: {$institutionId}");
                return;
            }
            
            $tables = VotingTable::where('institution_id', $institutionId)->get();
            
            // If election type is specified, only count records for that election type
            if ($electionTypeId) {
                $totalComputed = 0;
                foreach ($tables as $table) {
                    $computed = $table->votes()
                        ->where('election_type_id', $electionTypeId)
                        ->where('quantity', '>', 0)
                        ->count();
                    $totalComputed += $computed;
                }
                $institution->total_computed_records = $totalComputed;
            } else {
                // Count all records across election types
                $institution->total_computed_records = $tables->sum('computed_records');
            }
            
            $institution->total_annulled_records = $tables->sum('annulled_records');
            $institution->total_enabled_records = $tables->sum('enabled_records');
            $institution->save();
            
        } catch (\Exception $e) {
            Log::error("Error updating institution totals for {$institutionId}: " . $e->getMessage());
        }
    }
}