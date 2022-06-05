<?php

namespace FredBradley\TOPDesk;

use FredBradley\Cacher\Cacher;
use FredBradley\TOPDesk\Exceptions\ConfigNotFound;
use FredBradley\TOPDesk\Traits\Assets;
use FredBradley\TOPDesk\Traits\Changes;
use FredBradley\TOPDesk\Traits\Counts;
use FredBradley\TOPDesk\Traits\Incidents;
use FredBradley\TOPDesk\Traits\OperatorStats;
use FredBradley\TOPDesk\Traits\Persons;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Innovaat\Topdesk\Api;

class TOPDesk extends Api
{
    use Incidents, OperatorStats, Changes, Counts, Persons, Assets;

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    public static function query(): PendingRequest
    {
        return Http::topdeskAuth();
    }

    /**
     * TOPDesk constructor.
     *
     * @param  string  $endpoint
     * @param  int  $retries
     * @param  array  $guzzleOptions
     */
    public function __construct($endpoint = 'https://partnerships.topdesk.net/tas/', $retries = 5, $guzzleOptions = [])
    {
        $this->checkConfig();

        try {
            parent::__construct($this->endpointWithTrailingSlash(), $retries, $guzzleOptions);
            $this->useApplicationPassword(
                config('topdesk.application_username'),
                config('topdesk.application_password')
            );
        } catch (ConnectException $exception) {
            throw $exception;
        }
    }

    /**
     * Let the User know if they have forgotten to update their .env file.
     *
     * @throws ConfigNotFound
     */
    private function checkConfig()
    {
        foreach (config('topdesk') as $key => $config) {
            if ($config === null) {
                throw new ConfigNotFound("You need to set the config for env('topdesk.".$key."')", 400);
            }
            if ($config === '') {
                throw new ConfigNotFound(
                    "It seems unlikely that the env('topdesk.".$key."') should be an empty string!? I don't work with people like that!",
                    400
                );
            }
        }
    }

    /**
     * @param  string  $string
     * @return string
     */
    public function getArchiveReasonId(string $string): string
    {
        return $this->getArchiveReasons()->where('name', $string)->first()['id'];
    }

    /**
     * @return \Illuminate\Support\Collection
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getArchiveReasons(): Collection
    {
        return self::query()
                   ->get('api/archiving-reasons')
                   ->throw()
                   ->collect();
    }

    /**
     * Let's hold the end users hands,
     * and if they fall at the first hurdle,
     * we won't say a thing!
     *
     * @return string
     */
    private function endpointWithTrailingSlash(): string
    {
        return rtrim(config('topdesk.endpoint'), '/\\').'/';
    }

    /**
     * Does some repetitive lifting for us. Calculates whether we should happily
     * rely on the Cache or to clear that cache object and fetch brand new data.
     *
     * It then returns the cacheKey back so the framework can use it.
     *
     * @param  string  $cacheKey
     * @param  bool  $forgetCache
     * @return string
     *
     * @throws \FredBradley\Cacher\Exceptions\FrameworkNotDetected
     */
    public function setupCacheObject(string $cacheKey, bool $forgetCache): string
    {
        if ($forgetCache || config('topdesk.ignore_cache')) {
            Cacher::forget($cacheKey);
        }

        return $cacheKey;
    }

    /**
     * Shorthand function to create requests with JSON body and query parameters.
     *
     * @param $method
     * @param  string  $uri
     * @param  array  $json
     * @param  array  $query
     * @param  array  $options
     * @param  bool  $decode  JSON decode response body (defaults to true).
     * @return mixed|ResponseInterface
     *
     * @deprecated We would rather use the Laravel HTTP Client Facade
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri = '', array $json = [], array $query = [], array $options = [], $decode = true)
    {
        try {
            $response = $this->client->request($method, $uri, array_merge([
                'json' => $json,
                'query' => $query,
            ], $options));
            // return $response->getStatusCode();

            return $decode ? json_decode((string) $response->getBody(), true) : (string) $response->getBody();
        } catch (ConnectException $exception) {
            abort(500, 'Connection to TOPdesk Failed');
        } catch (ServerException $exception) {
            if ($exception->getCode() === 503) {
                Log::info('TOPdesk is unavailable');
            }
            abort(417, 'TOPdesk is unavailable at this time...');
        }
    }
}
