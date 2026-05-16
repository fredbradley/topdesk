<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Collection;

trait IncidentActions
{
    // === Progress Trail ===

    public function getProgressTrail(string $incidentId, array $options = []): Collection
    {
        return collect($this->get('api/incidents/id/'.$incidentId.'/progresstrail', $options));
    }

    public function getProgressTrailByNumber(string $number, array $options = []): Collection
    {
        return collect($this->get('api/incidents/number/'.$number.'/progresstrail', $options));
    }

    public function getProgressTrailCount(string $incidentId): int
    {
        return (int) $this->get('api/incidents/id/'.$incidentId.'/progresstrail/count');
    }

    public function getProgressTrailCountByNumber(string $number): int
    {
        return (int) $this->get('api/incidents/number/'.$number.'/progresstrail/count');
    }

    // === Actions ===

    public function getIncidentActions(string $incidentId, array $options = []): Collection
    {
        return collect($this->get('api/incidents/id/'.$incidentId.'/actions', $options));
    }

    public function getIncidentActionsByNumber(string $number, array $options = []): Collection
    {
        return collect($this->get('api/incidents/number/'.$number.'/actions', $options));
    }

    public function getIncidentAction(string $incidentId, string $actionId): object
    {
        return $this->get('api/incidents/id/'.$incidentId.'/actions/'.$actionId);
    }

    public function deleteIncidentAction(string $incidentId, string $actionId): array|object
    {
        return $this->delete('api/incidents/id/'.$incidentId.'/actions/'.$actionId);
    }

    // === Requests ===

    public function getIncidentRequests(string $incidentId, array $options = []): Collection
    {
        return collect($this->get('api/incidents/id/'.$incidentId.'/requests', $options));
    }

    public function getIncidentRequestsByNumber(string $number, array $options = []): Collection
    {
        return collect($this->get('api/incidents/number/'.$number.'/requests', $options));
    }

    public function getIncidentRequest(string $incidentId, string $requestId): object
    {
        return $this->get('api/incidents/id/'.$incidentId.'/requests/'.$requestId);
    }

    public function deleteIncidentRequest(string $incidentId, string $requestId): array|object
    {
        return $this->delete('api/incidents/id/'.$incidentId.'/requests/'.$requestId);
    }

    // === Time Spent ===

    public function getTimeSpent(string $incidentId): Collection
    {
        return collect($this->get('api/incidents/id/'.$incidentId.'/timespent'));
    }

    public function getTimeSpentByNumber(string $number): Collection
    {
        return collect($this->get('api/incidents/number/'.$number.'/timespent'));
    }

    /**
     * @param  array  $data  Keys: timeSpent (minutes int), notes, entryDate, operator{id}, operatorGroup{id}, reason{id}
     */
    public function registerTimeSpent(string $incidentId, array $data): object
    {
        return $this->post('api/incidents/id/'.$incidentId.'/timespent', $data);
    }

    /**
     * @param  array  $data  Keys: timeSpent (minutes int), notes, entryDate, operator{id}, operatorGroup{id}, reason{id}
     */
    public function registerTimeSpentByNumber(string $number, array $data): object
    {
        return $this->post('api/incidents/number/'.$number.'/timespent', $data);
    }

    public function getTimeRegistrations(array $options = []): Collection
    {
        return collect($this->get('api/incidents/timeregistrations', $options));
    }

    public function getTimeRegistration(string $id): object
    {
        return $this->get('api/incidents/timeregistrations/'.$id);
    }

    // === Escalation / De-escalation ===

    public function escalateIncident(string $incidentId, string $reasonId): array|object
    {
        return $this->put('api/incidents/id/'.$incidentId.'/escalate', ['id' => $reasonId]);
    }

    public function escalateIncidentByNumber(string $number, string $reasonId): array|object
    {
        return $this->put('api/incidents/number/'.$number.'/escalate', ['id' => $reasonId]);
    }

    public function deescalateIncident(string $incidentId, string $reasonId): array|object
    {
        return $this->put('api/incidents/id/'.$incidentId.'/deescalate', ['id' => $reasonId]);
    }

    public function deescalateIncidentByNumber(string $number, string $reasonId): array|object
    {
        return $this->put('api/incidents/number/'.$number.'/deescalate', ['id' => $reasonId]);
    }

    // === Archive / Unarchive ===

    public function archiveIncident(string $incidentId, string $reasonId): array|object
    {
        return $this->put('api/incidents/id/'.$incidentId.'/archive', ['id' => $reasonId]);
    }

    public function archiveIncidentByNumber(string $number, string $reasonId): array|object
    {
        return $this->put('api/incidents/number/'.$number.'/archive', ['id' => $reasonId]);
    }

    public function unarchiveIncident(string $incidentId): array|object
    {
        return $this->put('api/incidents/id/'.$incidentId.'/unarchive');
    }

    public function unarchiveIncidentByNumber(string $number): array|object
    {
        return $this->put('api/incidents/number/'.$number.'/unarchive');
    }

    // === Attachments ===

    public function getIncidentAttachments(string $incidentId, array $options = []): Collection
    {
        return self::query()->get('api/incidents/id/'.$incidentId.'/attachments', $options)->throw()->collect();
    }

    public function getIncidentAttachmentsByNumber(string $number, array $options = []): Collection
    {
        return self::query()->get('api/incidents/number/'.$number.'/attachments', $options)->throw()->collect();
    }

    public function deleteIncidentAttachment(string $incidentId, string $attachmentId): array|object
    {
        return $this->delete('api/incidents/id/'.$incidentId.'/attachments/'.$attachmentId);
    }
}
