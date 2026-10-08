<?php

namespace Tests\Support;

use App\Models\Concerns\LogsDomainActivity;

/**
 * Widget de teste com auditoria ligada.
 */
class AuditedWidget extends Widget
{
    use LogsDomainActivity;

    protected $table = 'widgets';
}
