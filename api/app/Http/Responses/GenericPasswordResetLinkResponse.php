<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

/**
 * RF-04: mesma resposta para e-mail cadastrado ou não, para não revelar quem tem conta.
 */
class GenericPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse, SuccessfulPasswordResetLinkRequestResponse
{
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Se o e-mail estiver cadastrado, você receberá um link para redefinir a senha.',
        ]);
    }
}
