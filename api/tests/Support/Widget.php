<?php

namespace Tests\Support;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Model usado apenas nos testes da infraestrutura de tenant e auditoria.
 */
class Widget extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    public static function createTable(): void
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('name');
            $table->timestamps();
        });
    }
}
