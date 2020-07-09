<?php

namespace FredBradley\TOPDesk;

use FredBradley\TOPDesk\Exceptions\ConfigNotFound;
use FredBradley\TOPDesk\Traits\Incidents;
use Innovaat\Topdesk\Api;

class TOPDesk extends Api
{
    use Incidents;

    /**
     * TOPDesk constructor.
     * @param string $endpoint
     * @param int $retries
     * @param array $guzzleOptions
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
     * Let's the User know they have forgotten to update their .env file
     *
     * @throws ConfigNotFound
     */
    private function checkConfig()
    {
        foreach (config('topdesk') as $key => $config) {
            if ($config === null) {
                throw new ConfigNotFound("You need to set the config for env('topdesk." . $key . "')", 400);
            }
            if ($config === "") {
                throw new ConfigNotFound("It seems unlikely that the env('topdesk." . $key . "') should be an empty string!? I don't work with people like that!",
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
        return rtrim(config('topdesk.endpoint'), '/\\') . '/';
    }

}
