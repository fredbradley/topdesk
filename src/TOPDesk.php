<?php

namespace FredBradley\TOPDesk;

use FredBradley\TOPDesk\Exceptions\ConfigNotFound;
use FredBradley\TOPDesk\Traits\Changes;
use FredBradley\TOPDesk\Traits\Counts;
use FredBradley\TOPDesk\Traits\Incidents;
use FredBradley\TOPDesk\Traits\OperatorStats;
use FredBradley\TOPDesk\Traits\Persons;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Innovaat\Topdesk\Api;

class TOPDesk extends Api
{
    use Incidents, OperatorStats, Changes, Counts, Persons;

    /**
     * TOPDesk constructor.
     *
     * @param string $endpoint
     * @param int    $retries
     * @param array  $guzzleOptions
     */
    public function __construct($endpoint = 'https://partnerships.topdesk.net/tas/', $retries = 5, $guzzleOptions = [])
    {
        $this->checkConfig();

        parent::__construct($this->endpointWithTrailingSlash(), $retries, $guzzleOptions);
        $this->useApplicationPassword(
            config('topdesk.application_username'),
            config('topdesk.application_password')
        );
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
                throw new ConfigNotFound("It seems unlikely that the env('topdesk.".$key."') should be an empty string!? I don't work with people like that!",
                    400);
            }
        }
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
     * @param $method
     * @param string $uri
     * @param array $json
     * @param array $query
     * @param array $options
     * @param boolean $decode JSON decode response body (defaults to true).
     * @return mixed|ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri = '', array $json = [], array $query = [], array $options = [], $decode = true)
    {
        try {
            $response = $this->client->request($method, $uri, array_merge([
                'json' => $json,
                'query' => $query
            ], $options));

            return $decode ? json_decode((string)$response->getBody(), true) : (string)$response->getBody();
        } catch (ServerException $exception) {
            Log::error("TOPdesk Server Exception", [
                'status' => $exception->getCode(),
                'method' => $method,
                'uri' => $uri
            ]);
            return $exception;
        }
    }
}
