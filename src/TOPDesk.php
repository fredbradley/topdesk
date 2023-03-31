<?php

namespace FredBradley\TOPDesk;

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
use Illuminate\Support\Facades\Http;

class TOPDesk
{
    use Incidents, OperatorStats, Changes, Counts, Persons, Assets;

    private $client;

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
     * @param  string  $uri
     * @param  array  $data
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function delete(string $uri, array $data): array|object
    {
        return $this->process($this->setupResponse()->delete($uri, $data));
    }

    /**
     * @param  string  $uri
     * @param  array  $data
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function patch(string $uri, array $data): array|object
    {
        return $this->process($this->setupResponse()->patch($uri, $data));
    }

    /**
     * @param  string  $uri
     * @param  array  $data
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function put(string $uri, array $data): array|object
    {
        return $this->process($this->setupResponse()->put($uri, $data));
    }

    /**
     * @param  string  $uri
     * @param  array  $data
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function post(string $uri, array $data): array|object
    {
        return $this->process($this->setupResponse()->post($uri, $data));
    }

    /**
     * @param  string  $uri
     * @param  array  $query
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function get(string $uri, array $query = []): array|object
    {
        return $this->process($this->setupResponse()->get($uri, $query));
    }

    /**
     * @param  \Illuminate\Http\Client\Response  $response
     * @return array|object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function process(Response $response): array|object
    {
        if ($response->status() === \Illuminate\Http\Response::HTTP_NO_CONTENT) {
            return [];
        }

        return $response->throw()->object();
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function setupResponse(): PendingRequest
    {
        return Http::acceptJson()->withBasicAuth(
            config('topdesk.application_username'),
            config('topdesk.application_password')
        )->baseUrl($this->endpointWithTrailingSlash());
    }

    /**
     * @param  string  $string
     * @return mixed
     */
    public function getArchiveReasonId(string $string)
    {
        $result = $this->get('api/archiving-reasons');
        $results = collect($result);

        return $results->where('name', $string)->first()->id;
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
