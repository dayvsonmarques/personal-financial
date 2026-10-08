<?php

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = actingAsMember();
    $this->user->update(['name' => 'Pedro', 'email' => 'pedro@exemplo.test', 'password' => 'senha-atual-123']);
});

it('altera o nome (RF-05)', function () {
    $this->putJson('/api/v1/auth/user/profile-information', ['name' => 'Pedro Alves', 'email' => 'pedro@exemplo.test'])
        ->assertOk();

    expect($this->user->fresh())
        ->name->toBe('Pedro Alves')
        ->email_verified_at->not->toBeNull();
});

it('exige nova verificação ao trocar o e-mail', function () {
    Notification::fake();

    $this->putJson('/api/v1/auth/user/profile-information', ['name' => 'Pedro', 'email' => 'novo@exemplo.test'])
        ->assertOk();

    expect($this->user->fresh())
        ->email->toBe('novo@exemplo.test')
        ->email_verified_at->toBeNull();

    Notification::assertSentTo($this->user->fresh(), VerifyEmail::class);
});

it('troca a senha exigindo a senha atual', function () {
    $this->putJson('/api/v1/auth/user/password', [
        'current_password' => 'errada',
        'password' => 'nova-senha-123',
        'password_confirmation' => 'nova-senha-123',
    ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

    $this->putJson('/api/v1/auth/user/password', [
        'current_password' => 'senha-atual-123',
        'password' => 'nova-senha-123',
        'password_confirmation' => 'nova-senha-123',
    ])->assertOk();

    expect(Hash::check('nova-senha-123', $this->user->fresh()->password))->toBeTrue();
});

it('encerra as outras sessões ao trocar a senha', function () {
    config(['session.driver' => 'database']);

    DB::table('sessions')->insert([
        'id' => 'sessao-de-outro-dispositivo',
        'user_id' => $this->user->id,
        'ip_address' => '10.0.0.2',
        'user_agent' => 'Outro navegador',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->putJson('/api/v1/auth/user/password', [
        'current_password' => 'senha-atual-123',
        'password' => 'nova-senha-123',
        'password_confirmation' => 'nova-senha-123',
    ])->assertOk();

    expect(DB::table('sessions')->where('id', 'sessao-de-outro-dispositivo')->exists())->toBeFalse();
});
