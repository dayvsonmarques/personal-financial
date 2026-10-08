<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Vínculo usuário ↔ organização. No MVP o único papel é `owner` (D1).
 */
class OrganizationMember extends Pivot
{
    public const ROLE_OWNER = 'owner';

    protected $table = 'organization_members';
}
