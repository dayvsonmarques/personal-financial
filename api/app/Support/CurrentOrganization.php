<?php

namespace App\Support;

use App\Exceptions\MissingCurrentOrganization;
use App\Models\Organization;

/**
 * Organização (tenant) em uso na requisição ou no job atual.
 * Registrada como `scoped` no container: é reiniciada a cada requisição e job.
 */
class CurrentOrganization
{
    private ?Organization $organization = null;

    public function set(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function has(): bool
    {
        return $this->organization !== null;
    }

    public function get(): Organization
    {
        return $this->organization ?? throw new MissingCurrentOrganization;
    }

    public function id(): int
    {
        return $this->get()->id;
    }
}
