<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

// getSuppliers and getSupplier are already tested in GeneralTest.php.
// This file covers the remaining Supplier operations.

it('returns supplier lookup results as a cached Collection', function () {
    Http::fake(['*api/suppliers/lookup*' => Http::response([
        ['id' => 'sup-1', 'name' => 'Dell'],
        ['id' => 'sup-2', 'name' => 'HP'],
    ])]);

    $first = TOPDesk::getSupplierLookup();
    $second = TOPDesk::getSupplierLookup();

    expect($first)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSentCount(1); // cached after first call
});

it('passes options to the supplier lookup endpoint', function () {
    Http::fake(['*api/suppliers/lookup*' => Http::response([['id' => 'sup-1', 'name' => 'Dell']])]);

    TOPDesk::getSupplierLookup(['name' => 'Dell']);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/suppliers/lookup')
        && str_contains($r->url(), 'name=Dell')
    );
});

it('returns supplier contacts as a Collection', function () {
    Http::fake(['*api/supplierContacts*' => Http::response([
        ['id' => 'contact-1', 'surName' => 'Smith'],
        ['id' => 'contact-2', 'surName' => 'Jones'],
    ])]);

    $result = TOPDesk::getSupplierContacts();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/supplierContacts'));
});

it('fetches a single supplier contact by id', function () {
    Http::fake(['*api/supplierContacts/*' => Http::response(['id' => 'contact-1', 'surName' => 'Smith'])]);

    $result = TOPDesk::getSupplierContact('contact-1');

    expect($result)->toBeObject()->and($result->id)->toBe('contact-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/supplierContacts/contact-1'));
});
