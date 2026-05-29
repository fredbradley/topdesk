<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;

trait General
{
    public function getApiVersion(): string
    {
        return $this->get('api/version')->version;
    }

    public function getProductVersion(): object
    {
        return $this->get('api/productVersion');
    }

    /**
     * @param  string  $index  Currently only 'incidents' is supported
     */
    public function search(string $query, string $index = 'incidents', int $start = 0): Collection
    {
        $response = $this->get('api/search', ['query' => $query, 'index' => $index, 'start' => $start]);

        return collect($response->results ?? []);
    }

    /**
     * @param  array  $options  Keys: $top, name (prefix filter), archived
     */
    public function getServiceWindows(array $options = [], bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('service_windows', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::hours(1), fn () => self::query()->get('api/serviceWindow/lookup/', $options)->throw()->collect());
    }

    public function getServiceWindow(string $id): object
    {
        return $this->get('api/serviceWindow/lookup/'.$id);
    }

    public function getEmail(string $id): object
    {
        return $this->get('api/emails/id/'.$id);
    }

    public function deleteEmail(string $id): array|object
    {
        return $this->delete('api/emails/id/'.$id);
    }

    public function getCountries(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('countries', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(4), fn () => self::query()->get('api/countries')->throw()->collect());
    }

    public function getLanguages(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('languages', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(4), fn () => self::query()->get('api/languages')->throw()->collect());
    }
}
