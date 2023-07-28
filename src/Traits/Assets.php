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
     * @return mixed
     */
    public function getAssetTemplateId(string $name): string
    {
        return Cacher::remember('assetTemplateId_'.$name, EasyMinutes::weeks(1), function () use ($name) {
            $return = $this->get('api/assetmgmt/templates')->dataSet;

            return collect($return)->where('text', '=', $name)->first()->id;
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
     * @param string $templateId
     * @param string $assetID
     * @param array $data
     * @return array|object
     * @throws RequestException
     */
    public function updateAssetByTemplateId(string $templateId, string $assetID, array $data):array|object
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
