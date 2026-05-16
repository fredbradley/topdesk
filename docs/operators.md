# Operators

## Looking up operators

```php
// By network login name (cached)
$operator = TOPDesk::getOperatorByUsername('jsmith');

// By UUID — optionally limit fields returned
$operator = TOPDesk::getOperatorById($uuid, fields: ['id', 'name', 'email']);

// The authenticated API user's own operator record
$me = TOPDesk::getCurrentOperator();

// Just the UUID of the current operator
$myId = TOPDesk::getCurrentOperatorId();

// Typeahead/lookup list
$results = TOPDesk::getOperatorLookup(['query' => 'Jane']);
```

---

## Creating and updating operators

```php
$operator = TOPDesk::createOperator([
    'surName'          => 'Doe',
    'firstName'        => 'Jane',
    'networkLoginName' => 'jdoe',
    'email'            => 'jdoe@example.com',
]);

TOPDesk::updateOperator($operator->id, [
    'phoneNumber' => '+44 1234 000000',
]);
```

---

## Archiving

```php
TOPDesk::archiveOperator($operatorId);
TOPDesk::unarchiveOperator($operatorId);
```

---

## Operator groups

```php
// List all operator groups
$groups = TOPDesk::getOperatorGroups();

// Single group by UUID
$group = TOPDesk::getOperatorGroupById($groupId);

// Resolve a group UUID by its display name (cached)
$groupId = TOPDesk::getOperatorGroupId('I.T. Services');

// All operators in a group (by display name)
$operators = TOPDesk::getOperatorsByOperatorGroup('I.T. Services');

// All operators in a group (by UUID)
$operators = TOPDesk::getOperatorGroupOperators($groupId);

// Typeahead/lookup list
$lookup = TOPDesk::getOperatorGroupLookup(['query' => 'IT']);
```

### Managing operator groups

```php
$group = TOPDesk::createOperatorGroup(['groupName' => 'Networking']);

TOPDesk::updateOperatorGroup($groupId, ['groupName' => 'Network & Infrastructure']);

TOPDesk::archiveOperatorGroup($groupId);
TOPDesk::unarchiveOperatorGroup($groupId);
```

---

## Permission groups

```php
$permissions = TOPDesk::getPermissionGroups();
```

---

## Operator statistics

These methods give per-operator and per-group counts, useful for dashboards.

### Counts by operator group

```php
// Open ticket count per operator in a group
$openCounts = TOPDesk::openCountsForOperatorGroup('I.T. Services');
// Returns: ['jsmith' => 12, 'jdoe' => 7, ...]

// Active (in-progress) ticket count per operator
$activeCounts = TOPDesk::activeCountsForOperatorGroup('I.T. Services');

// Closed ticket count per operator (day / week / month / total)
$closedCounts = TOPDesk::closedTicketCountsForOperatorGroup('I.T. Services');

// Exclude specific operators
$counts = TOPDesk::openCountsForOperatorGroup(
    name: 'I.T. Services',
    ignoreUsernames: ['api-user', 'monitoring'],
);
```

### Counts per operator

```php
// Returns array with closed_day, closed_week, closed_month, closed_total, open
$stats = TOPDesk::getClosedIncidentsForOperator($operatorId);

$openCount   = TOPDesk::countOpenTicketsByOperator($operatorId);
$activeCount = TOPDesk::countActiveTicketsbyOperator($operatorId);

// Closed count within a time period
$weekCount = TOPDesk::countClosedTicketsByTime($operatorId, 'week');
$monthCount = TOPDesk::countClosedTicketsByTime($operatorId, 'month');
```

---

## Using the Operator model

```php
use FredBradley\TOPDesk\Models\Operator;

$operator = Operator::findById($uuid);
$operator = Operator::findFirstByVariable('networkLoginName', 'jsmith');

$matches = Operator::whereVariableEquals('surName', 'Doe');
```
