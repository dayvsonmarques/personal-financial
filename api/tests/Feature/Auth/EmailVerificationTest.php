<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Route::middleware(['api', 'auth:sanctum', 'org', 'verified'])
        ->get('/api/v1/_test/verified-probe', fn () => ['ok' => true]);
});

it('envia o e-mail de verificação no cadastro (RF-02)', function () {
    Notification::fake();

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Ana Souza',
        'email' => 'ana@exemplo.test',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->assertCreated();

    Notification::assertSentTo(User::query()->sole(), VerifyEmail::class);
});

it('bloqueia rotas de domínio para usuário não verificado, mas libera /me', function () {
    $user = actingAsMember();
    $user->forceFill(['email_verified_at' => null])->save();

    $this->getJson('/api/v1/_test/verified-probe')->assertForbidden();
    $this->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email_verified_at', null);
});

it('libera rotas de domínio para usuário verificado', function () {
    actingAsMember();

    $this->getJson('/api/v1/_test/verified-probe')->assertOk();
});

it('verifica o e-mail pelo link assinado e volta para a SPA', function () {
    $user = actingAsMember();
    $user->forceFill(['email_verified_at' => null])->save();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    expect($url)->toStartWith('http://localhost:8080/api/v1/auth/email/verify/');

    $this->get($url)->assertRedirect('/?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('manda para a tela de login da SPA quem abre o link sem sessão', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->get($url)->assertRedirect('/entrar');
});

it('reenvia o e-mail de verificação', function () {
    Notification::fake();
    $user = actingAsMember();
    $user->forceFill(['email_verified_at' => null])->save();

    $this->postJson('/api/v1/auth/email/verification-notification')->assertStatus(202);

    Notification::assertSentTo($user, VerifyEmail::class);
});
