<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Mensagem usada tanto para "documento inexistente" quanto para "não é
     * dono" nas rotas que precisam ocultar a existência do documento (ver
     * view() abaixo). Os dois casos precisam ser textualmente idênticos, não
     * só ter o mesmo status HTTP — ver InsightsStreamController::stream().
     */
    public const NOT_FOUND_MESSAGE = 'Não encontrado.';

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $this->isOwner($user, $document);
    }

    /**
     * Determine whether the user can view the model.
     *
     * Nega como 404 (não 403): o documento contém dado clínico e o id é
     * adivinhável por enumeração se a resposta confirmar sua existência a
     * quem não é dono (ver R2 em ai-vitalfy/risks.md). Nota: update() acima
     * ainda responde 403 para não-dono — esse é um vazamento de existência
     * residual conhecido e aceito por ora (fora do escopo de R2, registrado
     * em ai-vitalfy/risks.md).
     */
    public function view(User $user, Document $document): Response
    {
        return $this->isOwner($user, $document)
            ? Response::allow()
            : Response::denyAsNotFound(self::NOT_FOUND_MESSAGE);
    }

    /**
     * Determine whether the user is the owner of the document.
     */
    private function isOwner(User $user, Document $document): bool
    {
        $documentUserId = $document->transcript()->withTrashed()->value('user_id');
        return $user->id === $documentUserId;
    }
}
