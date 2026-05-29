<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Models\Asset;
use FredBradley\TOPDesk\Models\BaseModel;
use FredBradley\TOPDesk\Models\Branch;
use FredBradley\TOPDesk\Models\Location;
use FredBradley\TOPDesk\Models\Operator;
use FredBradley\TOPDesk\Models\OperatorGroup;
use FredBradley\TOPDesk\Models\Person;
use FredBradley\TOPDesk\Models\PersonGroup;
use FredBradley\TOPDesk\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

// BaseModel is abstract; we test through concrete subclasses (Branch, Asset, Operator).

it('constructs a model instance from an array, assigning all properties', function () {
    $branch = new Branch(['id' => 'br-uuid', 'name' => 'London HQ']);

    expect($branch->id)->toBe('br-uuid');
    expect($branch->name)->toBe('London HQ');
});

it('constructs a model instance from a stdClass, assigning all properties', function () {
    $obj = (object) ['id' => 'br-uuid', 'name' => 'Manchester'];
    $branch = new Branch($obj);

    expect($branch->id)->toBe('br-uuid');
    expect($branch->name)->toBe('Manchester');
});

it('find() delegates to findById() and returns the model', function () {
    Http::fake(['*api/branches/id/*' => Http::response(['id' => 'br-uuid', 'name' => 'London HQ'])]);

    $result = Branch::find('br-uuid');

    expect($result)->toBeInstanceOf(Branch::class);
    expect($result->id)->toBe('br-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/branches/id/br-uuid'));
});

it('findById() uses the /id/ path for models that require it (Branch)', function () {
    Http::fake(['*api/branches/id/*' => Http::response(['id' => 'br-uuid', 'name' => 'Test Branch'])]);

    $result = Branch::findById('br-uuid');

    expect($result)->toBeInstanceOf(Branch::class)->and($result->id)->toBe('br-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/branches/id/br-uuid'));
});

it('findById() uses a direct path (no /id/) for Asset', function () {
    Http::fake(['*api/assetmgmt/assets/asset-uuid*' => Http::response(['id' => 'asset-uuid', 'name' => 'LAPTOP-001'])]);

    $result = Asset::findById('asset-uuid');

    expect($result)->toBeInstanceOf(Asset::class)->and($result->id)->toBe('asset-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid')
        && ! str_contains($r->url(), '/id/')
    );
});

it('findById() uses the /id/ path for Operator', function () {
    Http::fake(['*api/operators/id/*' => Http::response(['id' => 'op-uuid', 'surName' => 'Smith'])]);

    $result = Operator::findById('op-uuid');

    expect($result)->toBeInstanceOf(Operator::class)->and($result->id)->toBe('op-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operators/id/op-uuid'));
});

it('whereVariableEquals() queries the API and returns a Collection of model instances', function () {
    Http::fake(['*api/branches/*' => Http::response([
        ['id' => 'br-1', 'name' => 'London HQ'],
        ['id' => 'br-2', 'name' => 'London South'],
    ])]);

    $result = Branch::whereVariableEquals('name', 'London HQ');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    expect($result->first())->toBeInstanceOf(Branch::class);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/branches'));
});

it('whereVariableEquals() caches results and only calls the API once', function () {
    Http::fake(['*api/branches/*' => Http::response([['id' => 'br-1', 'name' => 'London HQ']])]);

    Branch::whereVariableEquals('name', 'London HQ');
    Branch::whereVariableEquals('name', 'London HQ');

    Http::assertSentCount(1);
});

it('whereVariableEquals() busts the cache when forgetCache is true', function () {
    Http::fake(['*api/branches/*' => Http::response([['id' => 'br-1', 'name' => 'London HQ']])]);

    Branch::whereVariableEquals('name', 'London HQ');
    Branch::whereVariableEquals('name', 'London HQ', forgetCache: true);

    Http::assertSentCount(2);
});

it('findFirstByVariable() returns the first matching model instance', function () {
    Http::fake(['*api/branches/*' => Http::response([
        ['id' => 'br-1', 'name' => 'London HQ'],
        ['id' => 'br-2', 'name' => 'London City'],
    ])]);

    $result = Branch::findFirstByVariable('name', 'London HQ');

    expect($result)->toBeInstanceOf(BaseModel::class);
    expect($result->id)->toBe('br-1');
});

it('findById() resolves the correct endpoint for Location', function () {
    Http::fake(['*api/locations/id/*' => Http::response(['id' => 'loc-uuid', 'name' => 'Server Room'])]);

    $result = Location::findById('loc-uuid');

    expect($result)->toBeInstanceOf(Location::class)->and($result->id)->toBe('loc-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/locations/id/loc-uuid'));
});

it('findById() resolves the correct endpoint for OperatorGroup', function () {
    Http::fake(['*api/operatorgroups/id/*' => Http::response(['id' => 'grp-uuid', 'groupName' => 'I.T. Services'])]);

    $result = OperatorGroup::findById('grp-uuid');

    expect($result)->toBeInstanceOf(OperatorGroup::class)->and($result->id)->toBe('grp-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorgroups/id/grp-uuid'));
});

it('findById() resolves the correct endpoint for Person', function () {
    Http::fake(['*api/persons/id/*' => Http::response(['id' => 'person-uuid', 'surName' => 'Smith'])]);

    $result = Person::findById('person-uuid');

    expect($result)->toBeInstanceOf(Person::class)->and($result->id)->toBe('person-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/persons/id/person-uuid'));
});

it('findById() resolves the correct direct path for PersonGroup (no /id/ prefix)', function () {
    Http::fake(['*api/persongroups/pg-uuid*' => Http::response(['id' => 'pg-uuid', 'name' => 'Students'])]);

    $result = PersonGroup::findById('pg-uuid');

    expect($result)->toBeInstanceOf(PersonGroup::class)->and($result->id)->toBe('pg-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/persongroups/pg-uuid')
        && ! str_contains($r->url(), '/id/')
    );
});

it('findById() resolves the correct direct path for Supplier (no /id/ prefix)', function () {
    Http::fake(['*api/suppliers/sup-uuid*' => Http::response(['id' => 'sup-uuid', 'name' => 'Dell'])]);

    $result = Supplier::findById('sup-uuid');

    expect($result)->toBeInstanceOf(Supplier::class)->and($result->id)->toBe('sup-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/suppliers/sup-uuid')
        && ! str_contains($r->url(), '/id/')
    );
});
