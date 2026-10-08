<?php

namespace App\Exceptions;

use RuntimeException;

class MissingCurrentOrganization extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Nenhuma organização atual definida para consultar dados de domínio.');
    }
}
