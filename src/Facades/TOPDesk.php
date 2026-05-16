<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Facades;

use Illuminate\Support\Facades\Facade;

/*
 * Pattern: Facade @method docblocks document only the public surface that
 * callers actually reach through the facade. IDE auto-completion and static
 * analysis (PHPStan, Larastan) resolve these stubs to the concrete class, so
 * each entry must match the real signature exactly. Group by domain for readability.
 *
 * --- HTTP primitives ---
 * @method static array|object get(string $uri, array $query = [])
 * @method static array|object post(string $uri, array $data = [])
 * @method static array|object put(string $uri, array $data = [])
 * @method static array|object patch(string $uri, array $data = [])
 * @method static array|object delete(string $uri, array $data = [])
 * @method static \Illuminate\Http\Client\PendingRequest query()
 *
 * --- Incidents ---
 * @method static array|object getListOfIncidents(array $options = [])
 * @method static object getIncident(string $topdeskIncidentNumber)
 * @method static object createIncident(array $options)
 * @method static object updateIncident(string $id, array $data)
 * @method static object updateIncidentByNumber(string $number, array $data)
 * @method static array|object archiveIncident(string $incidentId, string $reasonId)
 * @method static array|object unarchiveIncident(string $incidentId)
 * @method static array|object escalateIncident(string $incidentId, string $reasonId)
 * @method static array|object deescalateIncident(string $incidentId, string $reasonId)
 * @method static \Illuminate\Support\Collection getProgressTrail(string $incidentId, array $options = [])
 * @method static \Illuminate\Support\Collection getIncidentActions(string $incidentId, array $options = [])
 * @method static \Illuminate\Support\Collection getIncidentRequests(string $incidentId, array $options = [])
 * @method static \Illuminate\Support\Collection getIncidentAttachments(string $incidentId, array $options = [])
 * @method static object registerTimeSpent(string $incidentId, array $data)
 * @method static \Illuminate\Support\Collection getTimeSpent(string $incidentId)
 * @method static \Illuminate\Support\Collection getOpenIncidentsByOperatorGroupId(string $operatorGroupId, string $processingStatus = 'Open', bool $forgetCache = false)
 * @method static int getNumIncidents(string $fiql, array $options = [])
 *
 * --- Incident lookups ---
 * @method static \Illuminate\Support\Collection getCallTypes(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getDurations(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getEntryTypes(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getImpacts(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getPriorities(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getUrgencies(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getClosureCodes(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getEscalationReasons(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getDeescalationReasons(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getAllProcessingStatuses(bool $forgetCache = false)
 * @method static string getProcessingStatusId(string $name, bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getSlas(array $options = [])
 * @method static \Illuminate\Support\Collection getArchiveReasons()
 * @method static string getArchiveReasonId(string $string)
 *
 * --- People ---
 * @method static object getPersonByUsername(string $username)
 * @method static object getPersonById(string $id)
 * @method static object createPerson(array $data)
 * @method static object updatePerson(string $id, array $data)
 * @method static object getCurrentPerson()
 * @method static \Illuminate\Support\Collection getPersonGroups(array $query = [])
 *
 * --- Operators ---
 * @method static object getOperatorByUsername(string $username, bool $forgetCache = false)
 * @method static object getOperatorById(string $id, array $fields = [])
 * @method static object createOperator(array $data)
 * @method static object getCurrentOperator(array $fields = [])
 * @method static string getOperatorGroupId(string $name, bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getOperatorsByOperatorGroup(string $name)
 * @method static \Illuminate\Support\Collection getOperatorGroups(array $query = [])
 *
 * --- Assets ---
 * @method static object getAsset(string $assetId)
 * @method static array|object getListOfAssets(array $query = [])
 * @method static array|object createAssetByTemplateId(string $templateId, array $data)
 * @method static array|object updateAssetByTemplateId(string $templateId, string $assetID, array $data)
 * @method static array|object updateAsset(string $assetId, array $data)
 * @method static array|object archiveAsset(string $assetId)
 * @method static array|object getAssetAssignments(string $assetId)
 * @method static string getAssetTemplateId(string $name, bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getAssetStatuses(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getCardTypes(bool $forgetCache = false)
 *
 * --- Supporting files ---
 * @method static \Illuminate\Support\Collection getBranches(array $query = [])
 * @method static object getBranch(string $id)
 * @method static object createBranch(array $data)
 * @method static \Illuminate\Support\Collection getLocations(array $query = [])
 * @method static object getLocation(string $id)
 * @method static \Illuminate\Support\Collection getDepartments(array $query = [])
 * @method static \Illuminate\Support\Collection getSuppliers(array $query = [])
 * @method static object getSupplier(string $id)
 *
 * --- General ---
 * @method static string getApiVersion()
 * @method static object getProductVersion()
 * @method static \Illuminate\Support\Collection search(string $query, string $index = 'incidents', int $start = 0)
 * @method static \Illuminate\Support\Collection getCountries(bool $forgetCache = false)
 * @method static \Illuminate\Support\Collection getLanguages(bool $forgetCache = false)
 *
 * --- Cache ---
 * @method static string setupCacheObject(string $cacheKey, bool $forgetCache)
 */
class TOPDesk extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'topdesk';
    }
}
