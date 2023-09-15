<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasyMinutes;
use Illuminate\Http\Client\RequestException;

/**
 * Trait Assets.
 */
trait Assets
{
    /**
     * @param  string  $name
     * @return string
     */
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
     * @param  string  $assetID
     * @param  string  $incidentID
     * @return object
     *
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
     * @param  string  $templateId
     * @param  string  $assetID
     * @param  array  $data
     * @return array|object
     *
     * @throws RequestException
     */
    public function updateAssetByTemplateId(string $templateId, string $assetID, array $data): array|object
    {
        return $this->patch('api/assetmgmt/assets/templateId/'.$templateId.'/'.$assetID, $data);
    }

    /**
     * @param  string  $templateId
     * @param  array  $data
     * @return array|object
     */
    public function createAssetByTemplateId(string $templateId, array $data)
    {
        return $this->post('api/assetmgmt/assets/templateId/'.$templateId, $data);
    }
}
