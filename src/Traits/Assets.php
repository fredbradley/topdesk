<?php


namespace FredBradley\TOPDesk\Traits;


/**
 * Trait Assets
 * @package FredBradley\TOPDesk\Traits
 */
trait Assets
{
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
