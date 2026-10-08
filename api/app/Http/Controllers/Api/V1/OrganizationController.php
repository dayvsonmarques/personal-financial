<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Support\CurrentOrganization;

class OrganizationController extends Controller
{
    public function __construct(private CurrentOrganization $current) {}

    public function show(): OrganizationResource
    {
        return new OrganizationResource($this->current->get());
    }

    public function update(UpdateOrganizationRequest $request): OrganizationResource
    {
        $organization = $this->current->get();
        $organization->update($request->validated());

        return new OrganizationResource($organization);
    }
}
