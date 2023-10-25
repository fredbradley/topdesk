<?php

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Trait Assets.
 */
trait Assets
{
    public function getAssetTemplateId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('assetTemplateId_'.$name, $forgetCache);

        return Cache::rememberForever($cacheKey, function () use ($name) {
            $return = self::query()->get('api/assetmgmt/templates')->throw()->collect();

            $result = collect($return['dataSet']);

            return $result->where('text', '=', $name)->first()['id'];
        });
    }

    public function getListOfAssets($query = []): object
    {
        return $this->get('api/assetmgmt/assets', $query);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
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
}
