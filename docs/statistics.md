# Statistics and Counts

These methods are useful for building dashboards, reports, and monitors.

## Incident counts by operator group

```php
// Tickets logged today
$count = TOPDesk::countTicketsLoggedtoday('I.T. Services');

// Currently open tickets
$count = TOPDesk::countOpenTickets('I.T. Services');

// Tickets due this week
$count = TOPDesk::countTicketsDueThisWeek('I.T. Services');

// Tickets that have breached their SLA
$count = TOPDesk::countBreachedTickets('I.T. Services');

// Unassigned tickets in a group
$count = TOPDesk::countUnassignedTickets('I.T. Services');

// Count by a specific processing status name
$count = TOPDesk::countTicketsByStatus('Waiting for user', 'I.T. Services');

// Count by processing status UUID
$statusId = TOPDesk::getProcessingStatusId('Waiting for user');
$count    = TOPDesk::countByProcessingStatusId($statusId, 'I.T. Services');
```

## FIQL-based count

For precise filtering, use `getNumIncidents` with a FIQL query string:

```php
$count = TOPDesk::getNumIncidents(
    '(processingStatus.name==Open);(operatorGroup.name==I.T. Services)'
);

$count = TOPDesk::getNumIncidents(
    '(caller.id=='.$personId.')',
    ['pageSize' => 1],
);
```

## Counts per operator

```php
$openCount   = TOPDesk::countOpenTicketsByOperator($operatorId);
$activeCount = TOPDesk::countActiveTicketsbyOperator($operatorId);

// Closed within a time window ('day', 'week', 'month')
$weekCount = TOPDesk::countClosedTicketsByTime($operatorId, 'week');

// Resolved change activities for an operator
$changeCount = TOPDesk::countResolvesByTime($operatorId, 'week');

// Waiting change activities for an operator
$waitingCount = TOPDesk::countWaitingChangeActivitiesByOperatorId($operatorId);
```

## Per-operator breakdown for a group

These methods return an associative array keyed by network login name:

```php
// Open ticket count per operator
$openCounts = TOPDesk::openCountsForOperatorGroup('I.T. Services');
// ['jsmith' => 5, 'jdoe' => 12, ...]

// Active (in-progress) ticket count per operator
$activeCounts = TOPDesk::activeCountsForOperatorGroup('I.T. Services');

// Closed ticket counts per operator (day/week/month/total + open)
$closedCounts = TOPDesk::closedTicketCountsForOperatorGroup('I.T. Services');
// ['jsmith' => ['closed_day' => 3, 'closed_week' => 18, ...], ...]

// Exclude service accounts or absent operators
$counts = TOPDesk::openCountsForOperatorGroup(
    name: 'I.T. Services',
    ignoreUsernames: ['api-bot', 'monitoring'],
);
```

## Full closed-incident stats for one operator

```php
$stats = TOPDesk::getClosedIncidentsForOperator($operatorId);

// Returns:
// [
//   'closed_day'   => 3,
//   'closed_week'  => 18,
//   'closed_month' => 67,
//   'closed_total' => 412,
//   'open'         => 5,
// ]
```

Results are cached for 5 minutes.
