<?php

namespace FredBradley\TOPDesk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static get(string $string, int[] $array): object
 * @method static post(string $string, array $array): object
 * @method static request(string $string, string $string1, string[] $array): object
 * @method static patch(string $string, array $array): object
 * @method static getProcessingStatusid(string $string): object
 * @method static createIncident(array $params): object
 * @method static getPersonByUsername(mixed $username)
 * @method static array|object updateAssetByTemplateId(string $templateId, string $assetID, array $data)
 * @method static array|object createAssetByTemplateId(string $templateId, array $data)
 */
class TOPDesk extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'topdesk';
    }
}
