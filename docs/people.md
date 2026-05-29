# People

## Looking up persons

```php
// By network login name (throws PersonNotFound if no match)
$person = TOPDesk::getPersonByUsername('jsmith');

// By TOPdesk person UUID
$person = TOPDesk::getPersonById($uuid);

// The authenticated API user's own person record
$me = TOPDesk::getCurrentPerson();
```

`getPersonByUsername` throws `FredBradley\TOPDesk\Exceptions\PersonNotFound` (HTTP 404) when the username is not found, so you can catch it at the HTTP layer:

```php
try {
    $person = TOPDesk::getPersonByUsername($username);
} catch (\FredBradley\TOPDesk\Exceptions\PersonNotFound $e) {
    // 404 response — person does not exist in TOPdesk
}
```

---

## Creating and updating persons

```php
$person = TOPDesk::createPerson([
    'surName'          => 'Smith',
    'firstName'        => 'John',
    'networkLoginName' => 'jsmith',
    'email'            => 'jsmith@example.com',
    'branch'           => ['id' => $branchId],
]);

TOPDesk::updatePerson($person->id, [
    'phoneNumber' => '+44 1234 567890',
    'department'  => ['id' => $departmentId],
]);
```

---

## Archiving

```php
TOPDesk::archivePerson($personId);
TOPDesk::unarchivePerson($personId);
```

---

## Counts and lookups

```php
// Total number of persons in the system
$total = TOPDesk::getPersonCount();

// Typeahead/lookup list (e.g. for autocomplete)
$results = TOPDesk::getPersonLookup(['query' => 'Smith']);
```

---

## Person groups

```php
// List all person groups
$groups = TOPDesk::getPersonGroups();

// Single group by UUID
$group = TOPDesk::getPersonGroup($groupId);

// Members of a group
$members = TOPDesk::getPersonGroupPersons($groupId);

// Groups a specific person belongs to
$myGroups = TOPDesk::getPersonGroupsForPerson($personId);
```

### Managing person groups

```php
$group = TOPDesk::createPersonGroup(['groupName' => 'Finance']);

TOPDesk::updatePersonGroup($groupId, ['groupName' => 'Finance & Payroll']);

TOPDesk::archivePersonGroup($groupId);
TOPDesk::unarchivePersonGroup($groupId);

$lookup = TOPDesk::getPersonGroupLookup(['query' => 'Fin']);
```

---

## Using the Person model

The `Person` model wraps the API with a 5-minute cache and typed access:

```php
use FredBradley\TOPDesk\Models\Person;

$person = Person::findById($uuid);
$person = Person::findFirstByVariable('networkLoginName', 'jsmith');

// Filtered collection
$matches = Person::whereVariableEquals('surName', 'Smith');
```
