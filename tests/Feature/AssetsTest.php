<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('fetches an asset by id via GET api/assetmgmt/assets/{id}', function () {
    Http::fake(['*api/assetmgmt/assets/*' => Http::response(['id' => 'asset-uuid', 'name' => 'LAPTOP-001'])]);

    $result = TOPDesk::getAsset('asset-uuid');

    expect($result)->toBeObject()->and($result->id)->toBe('asset-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid'));
});

it('lists assets via GET api/assetmgmt/assets', function () {
    Http::fake(['*api/assetmgmt/assets*' => Http::response(['dataSet' => [['id' => 'a-1']]])]);

    TOPDesk::getListOfAssets();

    Http::assertSent(fn ($r) => $r->method() === 'GET' &&
        str_contains($r->url(), 'api/assetmgmt/assets')
    );
});

it('updates an asset via POST (not PATCH) to api/assetmgmt/assets/{id}', function () {
    Http::fake(['*api/assetmgmt/assets/asset-uuid*' => Http::response(['id' => 'asset-uuid'])]);

    TOPDesk::updateAsset('asset-uuid', ['name' => 'Updated Name']);

    Http::assertSent(function ($r) {
        $path = parse_url($r->url(), PHP_URL_PATH);

        return $r->method() === 'POST'
            && str_ends_with($path, '/api/assetmgmt/assets/asset-uuid');
    });
});

it('creates an asset by template id via POST', function () {
    Http::fake(['*assetmgmt/assets/templateId/*' => Http::response(['id' => 'new-asset'])]);

    $result = TOPDesk::createAssetByTemplateId('tmpl-uuid', ['name' => 'LAPTOP-002']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-asset');
    Http::assertSent(fn ($r) => $r->method() === 'POST' &&
        str_contains($r->url(), 'assetmgmt/assets/templateId/tmpl-uuid')
    );
});

it('updates an asset by template id via PATCH', function () {
    Http::fake(['*assetmgmt/assets/templateId/*' => Http::response(['id' => 'asset-uuid'])]);

    TOPDesk::updateAssetByTemplateId('tmpl-uuid', 'asset-uuid', ['serialNumber' => 'SN999']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'assetmgmt/assets/templateId/tmpl-uuid/asset-uuid')
    );
});

it('archives an asset via POST to api/assetmgmt/assets/{id}/archive', function () {
    Http::fake(['*archive*' => Http::response('', 204)]);

    TOPDesk::archiveAsset('asset-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'POST' &&
        str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/archive')
    );
});

it('unarchives an asset via POST to api/assetmgmt/assets/{id}/unarchive', function () {
    Http::fake(['*unarchive*' => Http::response('', 204)]);

    TOPDesk::unarchiveAsset('asset-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/unarchive'));
});

it('deletes assets via POST to api/assetmgmt/assets/delete with an assetIds array', function () {
    Http::fake(['*assetmgmt/assets/delete*' => Http::response('', 204)]);

    TOPDesk::deleteAssets(['asset-1', 'asset-2']);

    Http::assertSent(fn ($r) => $r->method() === 'POST' &&
        str_contains($r->url(), 'assetmgmt/assets/delete')
    );
});

it('fetches asset assignments via GET', function () {
    Http::fake(['*assignments*' => Http::response([['id' => 'link-1']])]);

    TOPDesk::getAssetAssignments('asset-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/assignments'));
});

it('adds an asset assignment via PUT', function () {
    Http::fake(['*assignments*' => Http::response(['id' => 'link-new'])]);

    TOPDesk::addAssetAssignment('asset-uuid', ['person' => ['id' => 'person-uuid']]);

    Http::assertSent(fn ($r) => $r->method() === 'PUT' &&
        str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/assignments')
    );
});

it('removes an asset assignment via DELETE', function () {
    Http::fake(['*assignments/link-1*' => Http::response('', 204)]);

    TOPDesk::removeAssetAssignment('asset-uuid', 'link-1');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE' &&
        str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/assignments/link-1')
    );
});

it('returns asset statuses as a cached Collection', function () {
    Http::fake(['*assetmgmt/assetStatuses*' => Http::response([
        ['id' => 's-1', 'name' => 'In use'],
        ['id' => 's-2', 'name' => 'In stock'],
    ])]);

    $first = TOPDesk::getAssetStatuses();
    $second = TOPDesk::getAssetStatuses();

    expect($first)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSentCount(1); // second call served from cache
});

it('returns card types as a cached Collection', function () {
    Http::fake(['*assetmgmt/cardTypes*' => Http::response([['id' => 'ct-1', 'name' => 'Hardware']])]);

    $result = TOPDesk::getCardTypes();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('resolves an asset template id by display name', function () {
    Http::fake(['*assetmgmt/templates*' => Http::response([
        'dataSet' => [
            ['id' => 'tmpl-1', 'text' => 'Laptop'],
            ['id' => 'tmpl-2', 'text' => 'Monitor'],
        ],
    ])]);

    $id = TOPDesk::getAssetTemplateId('Laptop');

    expect($id)->toBe('tmpl-1');
});

it('returns asset history via GET', function () {
    Http::fake(['*history/pastItems*' => Http::response([['id' => 'hist-1']])]);

    TOPDesk::getAssetHistory('asset-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/history/pastItems'));
});

it('returns asset current items via GET to history/currentItems', function () {
    Http::fake(['*history/currentItems*' => Http::response([['id' => 'item-1', 'name' => 'LAPTOP-001']])]);

    TOPDesk::getAssetCurrentItems('asset-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/history/currentItems'));
});

it('assigns an incident to an asset via PUT to api/assetmgmt/assets/{id}/assignments', function () {
    Http::fake(['*assignments*' => Http::response(['id' => 'link-new'])]);

    $result = TOPDesk::assignIncidentToAsset('asset-uuid', 'inc-uuid');

    expect($result)->toBeObject();
    Http::assertSent(fn ($r) => $r->method() === 'PUT'
        && str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/assignments')
    );
});

it('links an incident to an asset via POST to api/assetmgmt/assets/linkedTask', function () {
    Http::fake(['*assetmgmt/assets/linkedTask*' => Http::response(['status' => 'ok'])]);

    $result = TOPDesk::linkIncidentToAsset('asset-uuid', 'inc-uuid');

    expect($result)->toBeObject();
    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_contains($r->url(), 'api/assetmgmt/assets/linkedTask')
    );
});

it('copies an asset via POST to api/assetmgmt/assets/{id}/copy', function () {
    Http::fake(['*assetmgmt/assets/asset-uuid/copy*' => Http::response(['id' => 'asset-copy'])]);

    $result = TOPDesk::copyAsset('asset-uuid');

    expect($result)->toBeObject()->and($result->id)->toBe('asset-copy');
    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_contains($r->url(), 'api/assetmgmt/assets/asset-uuid/copy')
    );
});

it('returns asset links via GET to api/assetmgmt/assetLinks', function () {
    Http::fake(['*assetmgmt/assetLinks*' => Http::response([['id' => 'link-1', 'sourceId' => 'asset-a']])]);

    $result = TOPDesk::getAssetLinks(['sourceId' => 'asset-a']);

    Http::assertSent(fn ($r) => $r->method() === 'GET'
        && str_contains($r->url(), 'api/assetmgmt/assetLinks')
    );
});

it('creates an asset link via POST to api/assetmgmt/assetLinks', function () {
    Http::fake(['*assetmgmt/assetLinks*' => Http::response(['id' => 'new-link'])]);

    $result = TOPDesk::createAssetLink(['sourceId' => 'asset-a', 'targetId' => 'asset-b', 'capabilityId' => 'cap-1']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-link');
    Http::assertSent(fn ($r) => $r->method() === 'POST'
        && str_contains($r->url(), 'api/assetmgmt/assetLinks')
    );
});

it('deletes an asset link via DELETE', function () {
    Http::fake(['*assetmgmt/assetLinks/link-1*' => Http::response('', 204)]);

    $result = TOPDesk::deleteAssetLink('link-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'DELETE'
        && str_contains($r->url(), 'api/assetmgmt/assetLinks/link-1')
    );
});

it('returns asset templates as a cached Collection', function () {
    Http::fake(['*assetmgmt/templates*' => Http::response([
        'dataSet' => [
            ['id' => 'tmpl-1', 'text' => 'Laptop'],
            ['id' => 'tmpl-2', 'text' => 'Monitor'],
        ],
    ])]);

    $result = TOPDesk::getAssetTemplates();

    expect($result)->toBeInstanceOf(Collection::class);
});
