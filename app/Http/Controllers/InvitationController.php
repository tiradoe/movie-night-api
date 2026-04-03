<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvitationRequest;
use App\Mail\ListCollaboratorInvite;
use App\Models\Invitation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class InvitationController extends Controller
{
    private Invitation $invitation;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function accept(Request $request, string $token)
    {
        try {
            $invitation = $this->invitation::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Invitation not found', 'status' => 'not_found'], 404);
        } catch (Throwable $e) {
            Log::error('Failed to accept invitation: '.$e->getMessage());

            return response()->json(['message' => 'Failed to accept invitation', 'status' => 'failed'], 500);
        }

        $user = Auth::user();
        if ($user) {
            $user->sharedLists()->attach($invitation->movie_list_id, ['role' => 'viewer']);
            $invitation->update(['status' => 'accepted']);
            $invitation->delete();
        } else {
            $invitation->update(['status' => 'accepted_login_pending']);

            return response()->json(['message' => 'Unauthorized', 'status' => 'pending'], 401);
        }

        return response()->json(['message' => 'Invitation accepted', 'status' => 'accepted']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invitation $invitation)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function decline()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateInvitationRequest $request)
    {
        $validated = $request->validated();
        $invitations = [];

        try {
            DB::transaction(function () use ($validated, &$invitations) {
                foreach ($validated['emails'] as $email) {
                    $invitations[] = Invitation::create([
                        'email' => $email,
                        'movie_list_id' => $validated['movie_list_id'],
                        'token' => Str::uuid(),
                        'expires_at' => now()->addDays(Invitation::EXPIRATION_DAYS),
                    ]);
                }
            });
        } catch (Exception $e) {
            logger()->error('Failed to create invitation: '.$e->getMessage());

            return response()->json(['message' => 'Failed to create invitations.'], 500);
        }

        foreach ($invitations as $invitation) {
            Mail::to($invitation->email)->queue(new ListCollaboratorInvite(Auth::user(), $invitation));
        }

        return response()->json(['message' => 'Invitation created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invitation $invitation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invitation $invitation)
    {
        //
    }
}
