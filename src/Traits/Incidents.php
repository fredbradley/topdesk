<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use FredBradley\TOPDesk\Exceptions\OperatorNotFound;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Symfony\Component\VarDumper\Dumper\esc;

/**
 * Trait Incidents.
 */
trait Incidents
{
    /**
     * @param  string  $topdeskIncidentNumber
     * @return object
     *
     * @deprecated use getIncident() instead
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getIncidentbyNumber(string $topdeskIncidentNumber): object
    {
        return $this->getIncident($topdeskIncidentNumber);
    }

    /**
     * @param  string  $topdeskIncidentNumber  either the UNID or Ticket Number
     * @return object
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getIncident(string $topdeskIncidentNumber): object
    {
        if (Str::isUuid($topdeskIncidentNumber)) {
            return $this->get('api/incidents/id/'.$topdeskIncidentNumber);
        }

        return $this->get('api/incidents/number/'.$topdeskIncidentNumber);
    }

    public function createNewFrom(string $topdeskIncidentNumber)
    {
        $incident = $this->getIncident($topdeskIncidentNumber);
        $unsets = ['id', 'number', 'asset', 'externalLinks', 'timeSpent', 'requests', 'caller'];
        $incident['callerLookup']['id'] = $incident['caller']['id'];

        foreach ($unsets as $unset) {
            unset($incident[$unset]);
        }
        $incident['category'] =
            [
                'id' => $incident['category']['id'],
            ];
        unset($incident['subcategory']['name']);

        dd($incident);

        $result = $this->createIncident($incident);

        return $result;
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createIncident(array $options): object
    {
        return $this->post('api/incidents', $options);
    }

    /**
     * @param  string  $username
     * @return \stdClass
     */
    public function getOperatorByUsername(string $username): \stdClass
    {
        return Cacher::remember('operator_'.$username, EasySeconds::months(1), function () use ($username) {
            Log::debug('Searching for Operator: '.$username);
            $result = $this->get('api/operators', [
                'page_size' => 1,
                'query' => '(networkLoginName=='.$username.')',
            ]);

            if (is_null($result)) {
                throw new OperatorNotFound('Could not find an operator with the username: '.$username, 422);
            }

            if (count($result) == 1) {
                return $result[0];
            }

            return $result;
        });
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getOperatorGroupId(string $name): string
    {
        return Cacher::remember('get_operator_group_name_'.$name, EasySeconds::months(1), function () use ($name) {
            $result = $this->get('api/operatorgroups/lookup', ['name' => $name]);

            return $result->results[0]->id;
        });
    }

    public function getOpenIncidentsByOperatorGroupId(string $operatorGroupId, string $processingStatus=null, array $fields=[]): array
    {
        $queries = [
            'operatorGroup.id=='.$operatorGroupId
        ];
        if (is_null($processingStatus)) {
            $queries[] = 'closed==false';
        } else {
            $queries[] = 'processingStatus.name=="'.($processingStatus).'"';
        }

        $customFieldsList = empty($fields) ? null : implode(',',$fields);
        $result = $this->get('api/incidents', [
            'pageSize' => 10,
            'query' => implode(";", $queries),
            'fields' => $customFieldsList
        ]);
        return $result;
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getProcessingStatusId(string $name): string
    {
        return Cacher::remember('getProcessingStatusId_'.$name, EasySeconds::weeks(1), function () use ($name) {
            return $this->getProcessingStatus($name)->id;
        });
    }

    /**
     * @param  string  $name
     * @return \stdClass
     *
     * @throws \Illuminate\Support\ItemNotFoundException
     */
    public function getProcessingStatus(string $name): \stdClass
    {
        return Cacher::remember('status_'.$name, EasySeconds::weeks(1), function () use ($name) {
            $result = $this->get('api/incidents/statuses');

            return collect($result)->where('name', $name)->firstOrFail();
        });
    }
}
