# Incidents

## CRUD

### List incidents

```php
$incidents = TOPDesk::getListOfIncidents([
    'start'     => 0,
    'page_size' => 50,
    'status'    => 'firstLine',
]);
```

Accepts all query parameters documented in the TOPdesk REST API (page_size, start, status, category, subcategory, operator, operatorGroup, caller, etc.).

### Fetch a single incident

```php
// By incident number (e.g. "I-1234-56789")
$incident = TOPDesk::getIncident('I-1234-56789');
```

### Create an incident

```php
$incident = TOPDesk::createIncident([
    'callerLookup'     => ['id' => $personId],
    'briefDescription' => 'Email not working',
    'entryType'        => ['name' => 'Phone'],
    'category'         => ['name' => 'Software'],
    'subcategory'      => ['name' => 'Email'],
    'operatorGroup'    => ['id' => $groupId],
]);

echo $incident->number; // "I-2024-00001"
echo $incident->id;
```

### Update an incident

```php
// By UUID
TOPDesk::updateIncident($incident->id, [
    'briefDescription' => 'Updated description',
    'operator'         => ['id' => $operatorId],
]);

// By incident number
TOPDesk::updateIncidentByNumber('I-1234-56789', [
    'processingStatus' => ['name' => 'Resolved'],
]);
```

---

## Actions, requests, and progress trail

### Progress trail

```php
$trail = TOPDesk::getProgressTrail($incidentId);

// By incident number
$trail = TOPDesk::getProgressTrailByNumber('I-1234-56789');

// Count only
$count = TOPDesk::getProgressTrailCount($incidentId);
```

### Operator actions

Actions are operator-visible notes attached to an incident.

```php
$actions = TOPDesk::getIncidentActions($incidentId);

$action = TOPDesk::getIncidentAction($incidentId, $actionId);

TOPDesk::deleteIncidentAction($incidentId, $actionId);
```

### Caller requests

Requests are the caller-visible side of the conversation.

```php
$requests = TOPDesk::getIncidentRequests($incidentId);

$request = TOPDesk::getIncidentRequest($incidentId, $requestId);

TOPDesk::deleteIncidentRequest($incidentId, $requestId);
```

### Attachments

```php
$attachments = TOPDesk::getIncidentAttachments($incidentId);

// By incident number
$attachments = TOPDesk::getIncidentAttachmentsByNumber('I-1234-56789');

TOPDesk::deleteIncidentAttachment($incidentId, $attachmentId);
```

---

## Time registration

```php
// Register time spent on an incident
TOPDesk::registerTimeSpent($incidentId, [
    'timeSpent'    => 30,       // minutes
    'enteredDate'  => '2024-06-01',
    'operator'     => ['id' => $operatorId],
    'reason'       => ['id' => $reasonId],
]);

// Fetch all time registrations for an incident
$timeEntries = TOPDesk::getTimeSpent($incidentId);

// Browse all time registrations across incidents
$all = TOPDesk::getTimeRegistrations(['pageSize' => 100]);
$entry = TOPDesk::getTimeRegistration($registrationId);
```

---

## Escalation and de-escalation

```php
// Get available reasons first
$reasons = TOPDesk::getEscalationReasons();
$reasonId = $reasons->where('name', 'SLA breach')->first()['id'];

TOPDesk::escalateIncident($incidentId, $reasonId);
TOPDesk::escalateIncidentByNumber('I-1234-56789', $reasonId);

$deReasons = TOPDesk::getDeescalationReasons();
TOPDesk::deescalateIncident($incidentId, $deReasonId);
```

---

## Archiving

```php
// Get available archive reasons
$reasons    = TOPDesk::getArchiveReasons();
$reasonId   = TOPDesk::getArchiveReasonId('Duplicate');

TOPDesk::archiveIncident($incidentId, $reasonId);
TOPDesk::archiveIncidentByNumber('I-1234-56789', $reasonId);

TOPDesk::unarchiveIncident($incidentId);
TOPDesk::unarchiveIncidentByNumber('I-1234-56789');
```

---

## Lookup lists

These return cached `Collection` instances with `id` and `name` keys.

```php
TOPDesk::getCallTypes();
TOPDesk::getEntryTypes();
TOPDesk::getDurations();
TOPDesk::getImpacts();
TOPDesk::getPriorities();
TOPDesk::getUrgencies();
TOPDesk::getClosureCodes();
TOPDesk::getEscalationReasons();
TOPDesk::getDeescalationReasons();
TOPDesk::getTimeSpentReasons();
TOPDesk::getCategories();
TOPDesk::getSlas();
TOPDesk::getSlaServices();
TOPDesk::getAllProcessingStatuses();
```

Pass `true` to force-refresh a cached list:

```php
$fresh = TOPDesk::getCallTypes(forgetCache: true);
```

### Resolve a processing status ID by name

```php
$statusId = TOPDesk::getProcessingStatusId('Waiting for user');
```

---

## Filtering with FIQL

TOPdesk uses FIQL (Feed Item Query Language) for filtering. Pass a `query` string in the options array:

```php
$open = TOPDesk::getListOfIncidents([
    'query' => '(processingStatus.name==Open);(operatorGroup.name==I.T. Services)',
]);

$count = TOPDesk::getNumIncidents(
    '(processingStatus.name==Open);(operator.id=='.$operatorId.')'
);
```

Operators: `==` (equals), `!=` (not equals), `=in=` (in list), `=out=` (not in list), `;` (AND), `,` (OR).

---

## Open incidents by operator group

```php
$incidents = TOPDesk::getOpenIncidentsByOperatorGroupId(
    operatorGroupId: $groupId,
    processingStatus: 'Open',
    forgetCache: false,
);
```
