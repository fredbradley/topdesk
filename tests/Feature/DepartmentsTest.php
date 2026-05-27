<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Http;

// getDepartments and createDepartment are already tested in GeneralTest.php.
// This file covers the remaining CRUD operations.

it('updates a department name via PATCH to api/departments/id/{id}', function () {
    Http::fake(['*api/departments/id/*' => Http::response(['id' => 'dept-1', 'name' => 'Sales'])]);

    $result = TOPDesk::updateDepartment('dept-1', 'Sales');

    expect($result)->toBeObject()->and($result->id)->toBe('dept-1');
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/departments/id/dept-1')
    );
});

it('deletes a department via DELETE to api/departments/id/{id}', function () {
    Http::fake(['*api/departments/id/*' => Http::response('', 204)]);

    $result = TOPDesk::deleteDepartment('dept-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'DELETE'
        && str_contains($r->url(), 'api/departments/id/dept-1')
    );
});

it('archives a department via PATCH to api/departments/id/{id}/archive', function () {
    Http::fake(['*api/departments/id/*/archive*' => Http::response('', 204)]);

    $result = TOPDesk::archiveDepartment('dept-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/departments/id/dept-1/archive')
    );
});

it('unarchives a department via PATCH to api/departments/id/{id}/unarchive', function () {
    Http::fake(['*api/departments/id/*/unarchive*' => Http::response(['id' => 'dept-1', 'name' => 'Finance'])]);

    $result = TOPDesk::unarchiveDepartment('dept-1');

    expect($result)->toBeObject()->and($result->id)->toBe('dept-1');
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/departments/id/dept-1/unarchive')
    );
});
