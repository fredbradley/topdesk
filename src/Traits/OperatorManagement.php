<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait OperatorManagement
{
    // === Operators ===

    public function getOperatorById(string $id, array $fields = []): object
    {
        $query = empty($fields) ? [] : ['fields' => implode(',', $fields)];

        return $this->get('api/operators/id/'.$id, $query);
    }

    /**
     * @param  array  $data  Keys: surName, firstName, firstInitials, loginName, loginPermission, password,
     *                       email, telephone, mobileNumber, networkLoginName, branch{id}, location{id},
     *                       department{id}, budgetHolder{id}, language{id}, jobTitle, linkedPerson{id},
     *                       firstLineCallOperator, secondLineCallOperator, changeCoordinator, etc.
     */
    public function createOperator(array $data): object
    {
        return $this->post('api/operators', $data);
    }

    public function updateOperator(string $id, array $data): object
    {
        return $this->patch('api/operators/id/'.$id, $data);
    }

    public function archiveOperator(string $id): array|object
    {
        return $this->patch('api/operators/id/'.$id.'/archive');
    }

    public function unarchiveOperator(string $id): array|object
    {
        return $this->patch('api/operators/id/'.$id.'/unarchive');
    }

    public function getCurrentOperator(array $fields = []): object
    {
        $query = empty($fields) ? [] : ['fields' => implode(',', $fields)];

        return $this->get('api/operators/current', $query);
    }

    public function getCurrentOperatorId(): string
    {
        return $this->get('api/operators/current/id');
    }

    /**
     * @param  array  $options  Keys: $top, name (prefix filter)
     */
    public function getOperatorLookup(array $options = []): Collection
    {
        return self::query()->get('api/operators/lookup', $options)->throw()->collect();
    }

    // === Operator Groups ===

    /**
     * @param  array  $query  Keys: start, page_size, query (FIQL), fields
     */
    public function getOperatorGroups(array $query = []): Collection
    {
        return self::query()->get('api/operatorgroups', $query)->throw()->collect();
    }

    public function getOperatorGroupById(string $id): object
    {
        return $this->get('api/operatorgroups/id/'.$id);
    }

    /**
     * @param  array  $data  Keys: groupName, branch{id}, contact{id}, accessRoles
     */
    public function createOperatorGroup(array $data): object
    {
        return $this->post('api/operatorgroups', $data);
    }

    public function updateOperatorGroup(string $id, array $data): object
    {
        return $this->patch('api/operatorgroups/id/'.$id, $data);
    }

    public function archiveOperatorGroup(string $id): array|object
    {
        return $this->patch('api/operatorgroups/id/'.$id.'/archive');
    }

    public function unarchiveOperatorGroup(string $id): array|object
    {
        return $this->patch('api/operatorgroups/id/'.$id.'/unarchive');
    }

    public function getOperatorGroupLookup(array $options = []): Collection
    {
        return self::query()->get('api/operatorgroups/lookup', $options)->throw()->collect();
    }

    public function getOperatorGroupOperators(string $id): Collection
    {
        return self::query()->get('api/operatorgroups/id/'.$id.'/operators')->throw()->collect();
    }

    // === Permission Groups ===

    public function getPermissionGroups(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('permission_groups', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/permissiongroups')->throw()->collect());
    }
}
