<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait Assets.
 */
trait Assets
{
    public function getAssetTemplateId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('assetTemplateId_'.$name, $forgetCache);

        // Pattern: Cache::remember with a long but finite TTL rather than rememberForever.
        // rememberForever entries are never evicted, which causes stale IDs to survive
        // cache flushes. A 30-day TTL is effectively permanent for this data but
        // respects the $forgetCache / ignore_cache flags via setupCacheObject().
        return self::cache()->remember($cacheKey, EasySeconds::days(30), function () use ($name) {
            $return = self::query()->get('api/assetmgmt/cardTypes')->throw()->collect();

            return collect($return["cardTypes"])->where("displayName", "=", $name)->first()["key"];
        });
    }

    public function getListOfAssets($query = []): object
    {
        return $this->get('api/assetmgmt/assets', $query);
    }

    /**
     * @throws RequestException
     */
    public function assignIncidentToAsset(string $assetID, string $incidentID): object
    {
        return $this->put('api/assetmgmt/assets/'.$assetID.'/assignments', [
            'linkType' => 'incident',
            'linkToId' => $incidentID,
        ]);
    }

    public function linkIncidentToAsset(string $assetID, string $incidentID): object
    {
        return $this->post('api/assetmgmt/assets/linkedTask', [
            'assetIds' => [
                $assetID,
            ],
            'taskId' => $incidentID,
            'taskType' => 'incident',
        ]);
    }

    /**
     * @throws RequestException
     */
    public function updateAssetByTemplateId(string $templateId, string $assetID, array $data): array|object
    {
        return $this->patch('api/assetmgmt/assets/templateId/'.$templateId.'/'.$assetID, $data);
    }

    /**
     * @return array|object
     */
    public function createAssetByTemplateId(string $templateId, array $data)
    {
        return $this->post('api/assetmgmt/assets/templateId/'.$templateId, $data);
    }

    public function getAsset(string $assetId): object
    {
        return $this->get('api/assetmgmt/assets/'.$assetId);
    }

    /**
     * Note: the Asset Management API uses POST (not PATCH) for field-level updates on individual assets.
     */
    public function updateAsset(string $assetId, array $data): array|object
    {
        return $this->post('api/assetmgmt/assets/'.$assetId, $data);
    }

    public function archiveAsset(string $assetId): array|object
    {
        return $this->post('api/assetmgmt/assets/'.$assetId.'/archive', []);
    }

    public function unarchiveAsset(string $assetId): array|object
    {
        return $this->post('api/assetmgmt/assets/'.$assetId.'/unarchive', []);
    }

    public function copyAsset(string $assetId): array|object
    {
        return $this->post('api/assetmgmt/assets/'.$assetId.'/copy', []);
    }

    /**
     * @param  array  $assetIds  Array of asset ID strings to delete
     */
    public function deleteAssets(array $assetIds): array|object
    {
        return $this->post('api/assetmgmt/assets/delete', ['assetIds' => $assetIds]);
    }

    // === Assignments ===

    public function getAssetAssignments(string $assetId): array|object
    {
        return $this->get('api/assetmgmt/assets/'.$assetId.'/assignments');
    }

    /**
     * @param  array  $data  Keys: branch{id}, location{id}, person{id}, personGroup{id}
     */
    public function addAssetAssignment(string $assetId, array $data): array|object
    {
        return $this->put('api/assetmgmt/assets/'.$assetId.'/assignments', $data);
    }

    public function removeAssetAssignment(string $assetId, string $linkId): array|object
    {
        return $this->delete('api/assetmgmt/assets/'.$assetId.'/assignments/'.$linkId);
    }

    // === Asset Links ===

    /**
     * @param  array  $query  Keys: sourceId, targetId, capabilityId
     */
    public function getAssetLinks(array $query = []): array|object
    {
        return $this->get('api/assetmgmt/assetLinks', $query);
    }

    /**
     * @param  array  $data  Keys: sourceId, targetId, capabilityId
     */
    public function createAssetLink(array $data): array|object
    {
        return $this->post('api/assetmgmt/assetLinks', $data);
    }

    public function deleteAssetLink(string $relationId): array|object
    {
        return $this->delete('api/assetmgmt/assetLinks/'.$relationId);
    }

    // === Lookups ===

    public function getAssetStatuses(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('asset_statuses', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/assetmgmt/assetStatuses')->throw()->collect());
    }

    public function getCardTypes(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('card_types', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/assetmgmt/cardTypes')->throw()->collect());
    }

    /**
     * @param  array  $options  Keys: archived, searchTerm, resourceCategory
     */
    public function getAssetTemplates(array $options = [], bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('asset_templates', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/assetmgmt/templates', $options)->throw()->collect());
    }

    // === Asset History ===

    public function getAssetHistory(string $assetId): array|object
    {
        return $this->get('api/assetmgmt/assets/'.$assetId.'/history/pastItems');
    }

    public function getAssetCurrentItems(string $assetId): array|object
    {
        return $this->get('api/assetmgmt/assets/'.$assetId.'/history/currentItems');
    }
}
