<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Transcript;
use App\Models\User;

class TranscriptPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    public function getConversations(User $user, Transcript $transcript): bool
    {
        return $this->isOwner($user, $transcript);
    }

    /**
     * BE-R1-07 (ai-vitalfy/action-plans/backend/R1.md): 404 para não-dono,
     * não 403 — mesmo cuidado de DocumentPolicy::view (ver R2 em
     * ai-vitalfy/risks.md). O id de um processamento em polling não deve
     * confirmar sua existência a quem não é dono.
     */
    public function viewStatus(User $user, Transcript $transcript): Response
    {
        return $this->isOwner($user, $transcript)
            ? Response::allow()
            : Response::denyAsNotFound('Não encontrado.');
    }

    /**
     * Determine whether the user is the owner of the transcript.
     */
    private function isOwner(User $user, Transcript $transcript): bool
    {
        return $user->id === $transcript->user_id;
    }
}
