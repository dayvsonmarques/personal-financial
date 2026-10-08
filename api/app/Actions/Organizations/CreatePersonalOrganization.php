<?php

namespace App\Actions\Organizations;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * RF-10 / D1: todo usuário nasce com uma organização pessoal, da qual é owner.
 */
class CreatePersonalOrganization
{
    public function handle(User $user): Organization
    {
        $organization = Organization::create([
            'name' => 'Finanças de '.Str::before($user->name, ' '),
        ]);

        $organization->members()->attach($user, ['role' => OrganizationMember::ROLE_OWNER]);

        $user->forceFill(['current_organization_id' => $organization->id])->save();

        return $organization;
    }
}
