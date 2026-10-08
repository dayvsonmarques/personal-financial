<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    $this->user = User::factory()->create(['email' => 'carla@exemplo.test']);
});

it('responde de forma idêntica para e-mail cadastrado ou não (RF-04)', function () {
    Notification::fake();

    $known = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'carla@exemplo.test']);
    $unknown = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'ninguem@exemplo.test']);

    $known->assertOk();
    $unknown->assertOk();
    expect($known->json())->toBe($unknown->json());

    Notification::assertSentToTimes($this->user, ResetPassword::class, 1);
});

it('envia link que aponta para a tela de redefinição da SPA', function () {
    Notification::fake();

    $this->postJson('/api/v1/auth/forgot-password', ['email' => 'carla@exemplo.test']);

    Notification::assertSentTo($this->user, ResetPassword::class, function (ResetPassword $notification) {
        $url = $notification->toMail($this->user)->actionUrl;

        return str_starts_with($url, 'http://localhost:8080/redefinir-senha?token=')
            && str_contains($url, 'email=carla%40exemplo.test');
    });
});

it('redefine a senha com token válido e não aceita o mesmo token de novo', function () {
    $token = Password::broker()->createToken($this->user);
    $payload = [
        'token' => $token,
        'email' => 'carla@exemplo.test',
        'password' => 'nova-senha-123',
        'password_confirmation' => 'nova-senha-123',
    ];

    $this->postJson('/api/v1/auth/reset-password', $payload)->assertOk();
    expect(Hash::check('nova-senha-123', $this->user->fresh()->password))->toBeTrue();

    $this->postJson('/api/v1/auth/reset-password', $payload)->assertUnprocessable();
});

it('limita a 3 solicitações por hora', function () {
    Notification::fake();

    foreach (range(1, 3) as $_) {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'carla@exemplo.test'])->assertOk();
    }

    $this->postJson('/api/v1/auth/forgot-password', ['email' => 'carla@exemplo.test'])->assertTooManyRequests();
});
