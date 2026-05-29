# Change Management

Change activities represent planned work items within TOPdesk's change management module.

## Listing change activities

```php
// All open, unblocked, unarchived change activities (sorted by planned final date)
$activities = TOPDesk::allOpenChangeActivities();

// Open activities assigned to a specific operator group
$unassigned = TOPDesk::unassignedWaitingChangeActivities('I.T. Services');

// Open activities for a specific operator (by username)
$mine = TOPDesk::waitingChangeActivitiesByUsername('jsmith');

// Open activities for a specific operator (by UUID)
$mine = TOPDesk::waitingChangeActivitiesByOperatorId($operatorId);
```

All methods return a `Collection` of activity objects from the API.

## Resolved activities

```php
// Resolved activities for an operator within a time window
$resolved = TOPDesk::resolvedChangeActivitiesByOperatorIdByTime($operatorId, 'Week');
$resolved = TOPDesk::resolvedChangeActivitiesByOperatorIdByTime($operatorId, 'Month');
```

The `$timeString` is passed to Carbon's `startOf()` method, so valid values are `'Day'`, `'Week'`, `'Month'`, `'Year'`, etc.

## Counts

```php
$count = TOPDesk::countWaitingChangeActivitiesByOperatorId($operatorId);
```

## Caching

All change activity methods cache their results for 10 minutes (open lists) or 1 hour (per-operator lists). Force-refresh is not currently exposed but you can clear cache keys manually or set `TOPdesk_ignore_cache=true` in your environment.
