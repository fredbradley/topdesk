<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait Counts
{
    public function countTicketsLoggedtoday(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return self::cache()->remember($cacheKey, EasySeconds::minutes(15), function () use ($operatorGroupName) {
            return $this->getNumIncidents('operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName).';creationDate=gt='.now()->startOfDay()->toIso8601String());
        });
    }

    public function countOpenTickets(string $operatorGroupName = 'Facilities', bool $forgetCache = false): int
    {
        $cacheKey = $this->setupCacheObject('openTickets_'.$operatorGroupName, $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName).';closed==false');
        });
    }

    public function countTicketsDueThisWeek(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return self::cache()->remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('targetDate=lt='.now()->endOfWeek()->toIso8601String().';closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName));
        });
    }

    public function countBreachedTickets(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return self::cache()->remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('targetDate=gt='.now()->toIso8601String().';closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName));
        });
    }

    public function countByProcessingStatusId(string $processingStatusId, string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$processingStatusId.$operatorGroupName);

        return self::cache()->remember($cacheKey, EasySeconds::minutes(5), fn () => $this->getNumIncidents(
            'closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName).';processingStatus.id=='.$processingStatusId
        ));
    }

    /**
     * Pattern: fetch only the `id` field and let the API paginate to 10 000 so
     * we keep the response payload small when we only care about the count.
     */
    public function getNumIncidents(string $fiql, array $options = []): int
    {
        $response = $this->get('api/incidents', array_merge([
            'pageSize' => 10000,
            'fields' => 'id',
            'query' => $fiql,
        ], $options));

        return collect($response)->count();
    }

    /**
     * @deprecated Use 'countClosedTicketsByTime' instead
     */
    public function countResolvesByTime(string $operatorId, string $timeString = 'week'): int
    {
        return $this->countClosedTicketsByTime($operatorId, $timeString);
    }

    public function countClosedTicketsByTime(string $operatorId, string $timeString = 'week'): int
    {
        return self::cache()->remember(
            'incidentsResolvedByOperatorAndTime_'.$operatorId.$timeString,
            EasySeconds::minutes(5),
            fn () => $this->getNumIncidents('operator.id=='.$operatorId.';closed==true;closedDate=gt='.now()->startOf($timeString)->toIso8601String())
        );
    }

    public function countOpenTicketsByOperator(string $operatorId): int
    {
        $incidents = self::cache()->remember(
            'countOpenTicketsByOperator_'.$operatorId,
            EasySeconds::minutes(5),
            fn () => $this->getNumIncidents('operator.id=='.$operatorId.';closed==false')
        );

        return $incidents + $this->countWaitingChangeActivitiesByOperatorId($operatorId);
    }

    public function countActiveTicketsbyOperator(string $operatorId): int
    {
        return self::cache()->remember(
            'countActiveIncidentsByOperatorID_'.$operatorId,
            EasySeconds::minutes(5),
            fn () => $this->getNumIncidents(
                'operator.id=='.$operatorId.';closed==false;processingStatus.id=in=('
                .$this->getProcessingStatusId('Logged').','
                .$this->getProcessingStatusId('In progress').','
                .$this->getProcessingStatusId('Updated by user').')'
            )
        );
    }

    public function countWaitingChangeActivitiesByOperatorId(string $operatorId): int
    {
        return self::cache()->remember(
            'countWaitingChangeActivitiesByOperator_'.$operatorId,
            EasySeconds::minutes(5),
            fn () => count($this->waitingChangeActivitiesByOperatorId($operatorId))
        );
    }

    public function countTicketsByStatus(string $statusName, string $operatorGroup = 'I.T. Services'): int
    {
        return self::cache()->remember(
            'countTicketsByStatus_'.$statusName,
            EasySeconds::minutes(5),
            fn () => $this->countByProcessingStatusId($this->getProcessingStatusId($statusName), $operatorGroup)
        );
    }

    public function countUnassignedTickets(string $operatorGroup = 'I.T. Services'): int
    {
        return self::cache()->remember(
            'countUnassignedITTickets'.$operatorGroup,
            EasySeconds::minutes(5),
            fn () => $this->getNumIncidents('operator.id=='.$this->getOperatorGroupId($operatorGroup).';closed==false')
        );
    }
}
