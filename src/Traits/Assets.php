<?php


namespace FredBradley\TOPDesk\Traits;


use Illuminate\Support\Facades\Cache;

/**
 * Trait Assets
 * @package FredBradley\TOPDesk\Traits
 */
trait Assets
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAssetTemplateId(string $name)
    {
        return Cache::rememberForever('assetTemplateId_' . $name, function () use ($name) {
            $return = $this->request('GET', 'api/assetmgmt/templates')[ 'dataSet' ];

            return collect($return)->where('text', '=', $name)->first()[ 'id' ];
        });
    }

    /**
     * @param string $templateId
     * @param string $assetID
     * @param array  $data
     *
     * @return mixed
     */
    public function updateAssetByTemplateId(string $templateId, string $assetID, array $data)
    {
        return $this->request("PATCH", "api/assetmgmt/assets/templateId/" . $templateId . "/" . $assetID, $data);
    }

    /**
     * @param string $templateId
     * @param array  $data
     *
     * @return mixed
     */
    public function createAssetByTemplateId(string $templateId, array $data)
    {
        return $this->request("POST", "api/assetmgmt/assets/templateId/" . $templateId, $data);
    }

}
