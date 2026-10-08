<?php

namespace App\Support\Audit;

use App\Models\Organization;
use App\Support\CurrentOrganization;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Actions\LogActivityAction as BaseLogActivityAction;

/**
 * Completa todo registro de auditoria com a organização e o IP (RF-100, RF-101)
 * e remove dados sensíveis das propriedades (seção 15).
 */
class LogActivityAction extends BaseLogActivityAction
{
    private const SENSITIVE_KEYS = ['password', 'password_confirmation', 'current_password', 'token', 'remember_token'];

    protected function beforeActivityLogged(Model $activity): void
    {
        parent::beforeActivityLogged($activity);

        $activity->setAttribute('organization_id', $this->resolveOrganizationId($activity));
        $activity->setAttribute('ip', app()->runningInConsole() && ! app()->runningUnitTests() ? null : request()->ip());

        if ($activity->getAttribute('properties') !== null) {
            $activity->setAttribute('properties', $activity->getAttribute('properties')->except(self::SENSITIVE_KEYS));
        }
    }

    private function resolveOrganizationId(Model $activity): ?int
    {
        $subject = $activity->getAttribute('subject');

        if ($subject instanceof Organization) {
            return $subject->id;
        }

        if ($subject instanceof Model && $subject->getAttribute('organization_id')) {
            return (int) $subject->getAttribute('organization_id');
        }

        $current = app(CurrentOrganization::class);
        if ($current->has()) {
            return $current->id();
        }

        $causer = $activity->getAttribute('causer');

        return $causer instanceof Model ? $causer->getAttribute('current_organization_id') : null;
    }
}
