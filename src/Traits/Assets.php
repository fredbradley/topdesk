<?php

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Facades\Cache;

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

    /**
     * @param  string  $templateId
     * @param  string  $assetID
     * @param  array  $data
     * @return mixed
     */
    public function updateAssetByTemplateId(string $templateId, string $assetID, array $data)
    {
        return $this->request('PATCH', 'api/assetmgmt/assets/templateId/'.$templateId.'/'.$assetID, $data);
    }

    /**
     * @param  string  $templateId
     * @param  array  $data
     * @return mixed
     */
    public function createAssetByTemplateId(string $templateId, array $data)
    {
        return $this->request('POST', 'api/assetmgmt/assets/templateId/'.$templateId, $data);
    }
}
