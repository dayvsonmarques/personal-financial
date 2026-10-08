<?php

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Cria (ou usa) uma organização, um usuário verificado membro dela,
 * autentica-o e define a organização atual para setup via models.
 */
function actingAsMember(?Organization $organization = null): User
{
    $organization ??= Organization::factory()->create();

    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $organization->members()->attach($user, ['role' => OrganizationMember::ROLE_OWNER]);

    app(CurrentOrganization::class)->set($organization);
    test()->actingAs($user);

    return $user;
}

/**
 * CA-01: um usuário de outra organização recebe 404 ao acessar o recurso.
 */
function expectCrossTenant404(string $method, string $uri, array $data = []): void
{
    actingAsMember();

    test()->json($method, $uri, $data)->assertNotFound();
}
