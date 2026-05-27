<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk;

use FredBradley\TOPDesk\Exceptions\ConfigNotFound;
use FredBradley\TOPDesk\Traits\Assets;
use FredBradley\TOPDesk\Traits\Branches;
use FredBradley\TOPDesk\Traits\Changes;
use FredBradley\TOPDesk\Traits\Counts;
use FredBradley\TOPDesk\Traits\Departments;
use FredBradley\TOPDesk\Traits\General;
use FredBradley\TOPDesk\Traits\IncidentActions;
use FredBradley\TOPDesk\Traits\IncidentLookups;
use FredBradley\TOPDesk\Traits\Incidents;
use FredBradley\TOPDesk\Traits\Locations;
use FredBradley\TOPDesk\Traits\OperatorManagement;
use FredBradley\TOPDesk\Traits\OperatorStats;
use FredBradley\TOPDesk\Traits\PersonManagement;
use FredBradley\TOPDesk\Traits\Persons;
use FredBradley\TOPDesk\Traits\Suppliers;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TOPDesk
{
    use Assets, Branches, Changes, Counts, Departments, General,
        IncidentActions, IncidentLookups, Incidents, Locations,
        OperatorManagement, OperatorStats, PersonManagement, Persons, Suppliers;

    public function __construct()
    {
        $this->checkConfig();
    }

    /**
     * Pattern: single authenticated entry-point for all HTTP calls.
     * The `topdeskAuth` macro (registered in TOPDeskServiceProvider) centralises
     * base-URL, credentials, and Accept header so no other code needs to know them.
     */
    public static function query(): PendingRequest
    {
        return Http::topdeskAuth();
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function get(string $uri, array $query = []): array|object
    {
        return $this->process(self::query()->get($uri, $query));
    }

    /**
     * @throws RequestException
     */
    public function post(string $uri, array $data = []): array|object
    {
        return $this->process(self::query()->post($uri, $data));
    }

    /**
     * @throws RequestException
     */
    public function put(string $uri, array $data = []): array|object
    {
        return $this->process(self::query()->put($uri, $data));
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function patch(string $uri, array $data = []): array|object
    {
        return $this->process(self::query()->patch($uri, $data));
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function delete(string $uri, array $data = []): array|object
    {
        return $this->process(self::query()->delete($uri, $data));
    }

    /**
     * Pattern: HTTP 204 No Content carries no body; return an empty array rather
     * than calling ->object() which would return null and break callers expecting
     * an object. All other responses are thrown on error then decoded.
     *
     * @throws RequestException
     */
    private function process(Response $response): array|object
    {
        if ($response->noContent()) {
            return [];
        }

        return $response->throw()->object();
    }

    public static function cache(): Repository
    {
        return Cache::store(config('topdesk.cache_driver'));
    }

    public function getArchiveReasonId(string $string): string
    {
        return $this->getArchiveReasons()->where('name', $string)->first()['id'];
    }

    /**
     * @throws RequestException
     */
    public function getArchiveReasons(): Collection
    {
        return self::query()->get('api/archiving-reasons')->throw()->collect();
    }

    /**
     * Pattern: cache-busting helper used by traits.
     * Forgets the entry so the subsequent self::cache()->remember() call misses and
     * re-populates — honouring both per-call $forgetCache and the global
     * ignore_cache config flag without duplicating the logic in every method.
     */
    public function setupCacheObject(string $cacheKey, bool $forgetCache): string
    {
        if ($forgetCache || config('topdesk.ignore_cache')) {
            Cache::forget($cacheKey);
        }

        return $cacheKey;
    }

    /**
     * @throws ConfigNotFound
     */
    private function checkConfig(): void
    {
        foreach (config('topdesk') as $key => $value) {
            if ($value === null) {
                throw new ConfigNotFound("Config value 'topdesk.{$key}' is not set.");
            }
            if ($value === '') {
                throw new ConfigNotFound("Config value 'topdesk.{$key}' must not be an empty string.");
            }
        }
    }

    private function endpointWithTrailingSlash(): string
    {
        return rtrim(config('topdesk.endpoint'), '/\\').'/';
    }
}
