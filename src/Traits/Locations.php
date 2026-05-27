<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;

trait Locations
{
    /**
     * @param  array  $query  Supports FIQL via 'query' key and field selection via '$fields'
     */
    public function getLocations(array $query = []): Collection
    {
        return self::query()->get('api/locations', $query)->throw()->collect();
    }

    public function getLocation(string $id): object
    {
        return $this->get('api/locations/id/'.$id);
    }

    /**
     * @param  array  $data  Keys: name, roomNumber, branch{id}, functionalUse{id}, type{id},
     *                       capacity, specification, budgetHolder{id}, buildingZone{id},
     *                       roomStatus{id}, roomSetup, majorLocation{id}, area, notes, etc.
     */
    public function createLocation(array $data): object
    {
        return $this->post('api/locations', $data);
    }

    public function updateLocation(string $id, array $data): object
    {
        return $this->patch('api/locations/id/'.$id, $data);
    }

    public function archiveLocation(string $id): array|object
    {
        return $this->patch('api/locations/id/'.$id.'/archive');
    }

    public function unarchiveLocation(string $id): array|object
    {
        return $this->patch('api/locations/id/'.$id.'/unarchive');
    }

    /**
     * @param  array  $options  Keys: $top (max 10000), name (prefix filter), archived
     */
    public function getLocationLookup(array $options = []): Collection
    {
        return self::query()->get('api/locations/lookup', $options)->throw()->collect();
    }

    public function getLocationTypes(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('location_types', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/locations/types')->throw()->collect());
    }

    public function getLocationStatuses(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('location_statuses', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/locations/statuses')->throw()->collect());
    }

    public function getBuildingZones(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('building_zones', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/locations/building_zones')->throw()->collect());
    }
}
