# Supporting Files

Supporting files are the reference data that incidents, persons, and assets are linked to: branches, locations, departments, and suppliers.

---

## Branches

```php
// List branches (optionally filter)
$branches = TOPDesk::getBranches(['query' => 'London']);

// Single branch by UUID
$branch = TOPDesk::getBranch($branchId);

// Resolve a branch UUID by display name (cached)
$branchId = TOPDesk::getBranchId('London HQ');

// Typeahead/lookup list
$lookup = TOPDesk::getBranchLookup(['query' => 'Lon']);
```

### Creating and updating branches

```php
$branch = TOPDesk::createBranch([
    'name'    => 'Manchester Office',
    'country' => ['id' => $countryId],
]);

TOPDesk::updateBranch($branchId, ['phone' => '+44 161 000 0000']);

TOPDesk::archiveBranch($branchId);
TOPDesk::unarchiveBranch($branchId);
```

### Branch metadata

```php
$designations   = TOPDesk::getBranchDesignations();
$buildingLevels = TOPDesk::getBranchBuildingLevels();
```

### Branch attachments

```php
$attachments = TOPDesk::getBranchAttachments($branchId);
TOPDesk::deleteBranchAttachment($branchId, $attachmentId);
```

---

## Locations

```php
$locations = TOPDesk::getLocations(['query' => 'Floor 3']);

$location = TOPDesk::getLocation($locationId);

$lookup = TOPDesk::getLocationLookup(['query' => 'Floor']);
```

### Creating and updating locations

```php
$location = TOPDesk::createLocation([
    'name'   => 'Floor 3 - East Wing',
    'branch' => ['id' => $branchId],
]);

TOPDesk::updateLocation($locationId, ['name' => 'Floor 3 - West Wing']);

TOPDesk::archiveLocation($locationId);
TOPDesk::unarchiveLocation($locationId);
```

### Location lookup lists

```php
$types    = TOPDesk::getLocationTypes();
$statuses = TOPDesk::getLocationStatuses();
$zones    = TOPDesk::getBuildingZones();
```

---

## Departments

```php
$departments = TOPDesk::getDepartments();

$dept = TOPDesk::createDepartment('Finance');

TOPDesk::updateDepartment($deptId, 'Finance & Payroll');

TOPDesk::archiveDepartment($deptId);
TOPDesk::unarchiveDepartment($deptId);
TOPDesk::deleteDepartment($deptId);
```

---

## Suppliers

```php
$suppliers = TOPDesk::getSuppliers(['query' => 'Dell']);

$supplier = TOPDesk::getSupplier($supplierId);

// Typeahead/lookup list (cached)
$lookup = TOPDesk::getSupplierLookup(['query' => 'Del']);
```

### Supplier contacts

```php
$contacts = TOPDesk::getSupplierContacts(['supplierId' => $supplierId]);

$contact = TOPDesk::getSupplierContact($contactId);
```

---

## Reference data

```php
// Countries (cached)
$countries = TOPDesk::getCountries();

// Languages (cached)
$languages = TOPDesk::getLanguages();
```

---

## Using models

```php
use FredBradley\TOPDesk\Models\Branch;
use FredBradley\TOPDesk\Models\Location;
use FredBradley\TOPDesk\Models\Supplier;

$branch   = Branch::findById($branchId);
$location = Location::findById($locationId);
$supplier = Supplier::findById($supplierId);

// Filtered collection
$branches = Branch::whereVariableEquals('name', 'London HQ');
```
