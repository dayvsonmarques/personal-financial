<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => $input['password'],
        ])->save();

        $this->keepCurrentSession($user);
        $this->endOtherSessions($user);
    }

    /**
     * As rotas do Fortify não passam pelo AuthenticateSession do Sanctum, que guarda o
     * hash da senha na sessão. Sem atualizá-lo aqui, a próxima chamada à API veria o hash
     * antigo e encerraria também a sessão de quem acabou de trocar a senha.
     */
    private function keepCurrentSession(User $user): void
    {
        if (! request()->hasSession()) {
            return;
        }

        request()->session()->put('password_hash_web', Auth::guard('web')->hashPasswordForCookie($user->getAuthPassword()));
    }

    /**
     * US-105: trocar a senha encerra as sessões dos outros dispositivos.
     */
    private function endOtherSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();
    }
}
