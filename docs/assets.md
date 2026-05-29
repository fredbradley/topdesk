# Assets

## Fetching assets

```php
// Single asset by UUID
$asset = TOPDesk::getAsset($assetId);

// List assets with optional filters
$assets = TOPDesk::getListOfAssets([
    'templateId' => $templateId,
    'archived'   => false,
]);
```

---

## Creating and updating assets

### Via template

Assets are created against a specific template that defines their fields.

```php
// Resolve the template UUID by name (cached for 30 days)
$templateId = TOPDesk::getAssetTemplateId('Laptop');

$asset = TOPDesk::createAssetByTemplateId($templateId, [
    'name'         => 'LAPTOP-0042',
    'serialNumber' => 'SN123456',
]);

TOPDesk::updateAssetByTemplateId($templateId, $asset->id, [
    'serialNumber' => 'SN999999',
]);
```

### Direct update

The Asset Management API uses POST (not PATCH) for field-level updates on individual assets.

```php
TOPDesk::updateAsset($assetId, [
    'name' => 'LAPTOP-0042-RENAMED',
]);
```

---

## Copying, archiving, and deleting

```php
TOPDesk::copyAsset($assetId);

TOPDesk::archiveAsset($assetId);
TOPDesk::unarchiveAsset($assetId);

// Delete one or more assets by UUID
TOPDesk::deleteAssets([$assetId1, $assetId2]);
```

---

## Assignments

Assignments link an asset to a person, branch, location, or person group.

```php
// Current assignments for an asset
$assignments = TOPDesk::getAssetAssignments($assetId);

// Add an assignment
TOPDesk::addAssetAssignment($assetId, [
    'person' => ['id' => $personId],
]);

// Or assign to a branch/location/person group
TOPDesk::addAssetAssignment($assetId, [
    'branch'   => ['id' => $branchId],
    'location' => ['id' => $locationId],
]);

// Remove an assignment
TOPDesk::removeAssetAssignment($assetId, $linkId);
```

---

## Asset links (relationships between assets)

```php
// List links — filter by source, target, or capability
$links = TOPDesk::getAssetLinks(['sourceId' => $assetId]);

// Create a link between two assets
TOPDesk::createAssetLink([
    'sourceId'     => $assetId,
    'targetId'     => $otherAssetId,
    'capabilityId' => $capabilityId,
]);

TOPDesk::deleteAssetLink($relationId);
```

---

## Linking incidents to assets

```php
// Assign an incident to an asset (adds to the asset's linked incidents)
TOPDesk::assignIncidentToAsset($assetId, $incidentId);

// Link using the task-linking endpoint
TOPDesk::linkIncidentToAsset($assetId, $incidentId);
```

---

## History

```php
// Full history of changes to an asset
$history = TOPDesk::getAssetHistory($assetId);

// Current field values snapshot
$current = TOPDesk::getAssetCurrentItems($assetId);
```

---

## Lookup lists

```php
// Asset statuses (e.g. In use, In stock, Broken)
$statuses = TOPDesk::getAssetStatuses();

// Card types
$cardTypes = TOPDesk::getCardTypes();

// All available asset templates
$templates = TOPDesk::getAssetTemplates();

// Archived templates
$archived = TOPDesk::getAssetTemplates(['archived' => true]);
```

Pass `forgetCache: true` to any of these to bust the cache:

```php
$statuses = TOPDesk::getAssetStatuses(forgetCache: true);
```

---

## Using the Asset model

```php
use FredBradley\TOPDesk\Models\Asset;

$asset = Asset::findById($assetId);

$matches = Asset::whereVariableEquals('serialNumber', 'SN123456');
```
