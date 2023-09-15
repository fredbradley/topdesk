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
use GuzzleHttp\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TOPDesk
{
    use Assets, Changes, Counts, Incidents, OperatorStats, Persons;

    private $client;

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
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->endpointWithTrailingSlash(),
            'auth' => [
                config('topdesk.application_username'),
                config('topdesk.application_password'),
            ],
        ]);
        $this->checkConfig();
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
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function delete(string $uri, array $data = []): array|object
    {
        return $this->process($this->setupResponse()->delete($uri, $data));
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function patch(string $uri, array $data = []): array|object
    {
        return $this->process($this->setupResponse()->patch($uri, $data));
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function put(string $uri, array $data = []): array|object
    {
        return $this->process($this->setupResponse()->put($uri, $data));
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function post(string $uri, array $data = []): array|object
    {
        return $this->process($this->setupResponse()->post($uri, $data));
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function get(string $uri, array $query = []): array|object
    {
        return $this->process($this->setupResponse()->get($uri, $query));
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function process(Response $response): array|object
    {
        if ($response->status() === \Illuminate\Http\Response::HTTP_NO_CONTENT) {
            return [];
        }

        return $response->throw()->object();
    }

    private function setupResponse(): PendingRequest
    {
        return Http::acceptJson()->withBasicAuth(
            config('topdesk.application_username'),
            config('topdesk.application_password')
        )->baseUrl($this->endpointWithTrailingSlash());
    }

    public function getArchiveReasonId(string $string): string
    {
        return $this->getArchiveReasons()->where('name', $string)->first()['id'];
    }

    /**
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
     * @param  string  $uri
     * @param  array  $json
     * @param  bool  $decode  JSON decode response body (defaults to true).
     * @return mixed|ResponseInterface
     *
     * @throws \Exception
     *
     * @deprecated Use specific HTTP OPTION method instead
     */
    public function request(
        $method,
        $uri = '',
        array $body = [],
        array $query = [],
        array $options = [],
        $decode = true
    ) {
        throw new \Exception('Method Deprecated. Use specific HTTP OPTION method instead.');
    }
}
