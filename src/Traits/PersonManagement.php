<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Collection;

trait PersonManagement
{
    /**
     * @param  array  $data  All person fields; at minimum surName is required.
     *                       Keys: surName, firstName, firstInitials, prefixes, networkLoginName,
     *                       tasLoginName, hasSspAccess, branch{id}, location{id}, department{id},
     *                       budgetHolder{id}, language{id}, phoneNumber, mobileNumber, email,
     *                       jobTitle, isManager, manager{id}, isCaller, etc.
     */
    public function createPerson(array $data): object
    {
        return $this->post('api/persons', $data);
    }

    public function updatePerson(string $id, array $data): object
    {
        return $this->patch('api/persons/'.$id, $data);
    }

    public function archivePerson(string $id): array|object
    {
        return $this->patch('api/persons/id/'.$id.'/archive');
    }

    public function unarchivePerson(string $id): array|object
    {
        return $this->patch('api/persons/id/'.$id.'/unarchive');
    }

    public function getCurrentPerson(): object
    {
        return $this->get('api/persons/current');
    }

    public function getPersonCount(): int
    {
        return (int) $this->get('api/persons/count');
    }

    /**
     * @param  array  $options  Keys: $top, name (prefix filter)
     */
    public function getPersonLookup(array $options = []): Collection
    {
        return self::query()->get('api/persons/lookup', $options)->throw()->collect();
    }

    public function getPersonGroupsForPerson(string $personId): Collection
    {
        return self::query()->get('api/persons/'.$personId.'/persongroups')->throw()->collect();
    }

    // === Person Groups ===

    /**
     * @param  array  $query  Keys: pageStart, pageSize, fields, sort, query (FIQL)
     */
    public function getPersonGroups(array $query = []): Collection
    {
        return self::query()->get('api/persongroups', $query)->throw()->collect();
    }

    public function getPersonGroup(string $id): object
    {
        return $this->get('api/persongroups/'.$id);
    }

    /**
     * @param  array  $options  Keys: pageStart, pageSize, fields, sort, query
     */
    public function getPersonGroupPersons(string $id, array $options = []): Collection
    {
        return self::query()->get('api/persongroups/'.$id.'/persons', $options)->throw()->collect();
    }

    /**
     * @param  array  $data  Keys: name, phoneNumber, fax, email, branch{id}, location{id},
     *                       department{id}, budgetHolder{id}, contactPerson{id}, optionalFields1, optionalFields2
     */
    public function createPersonGroup(array $data): object
    {
        return $this->post('api/persongroups', $data);
    }

    public function updatePersonGroup(string $id, array $data): object
    {
        return $this->patch('api/persongroups/'.$id, $data);
    }

    public function archivePersonGroup(string $id): array|object
    {
        return $this->post('api/persongroups/'.$id.'/archive', []);
    }

    public function unarchivePersonGroup(string $id): array|object
    {
        return $this->post('api/persongroups/id/'.$id.'/unarchive', []);
    }

    /**
     * @param  array  $options  Keys: $top, name (prefix filter)
     */
    public function getPersonGroupLookup(array $options = []): Collection
    {
        return self::query()->get('api/persongroups/lookup', $options)->throw()->collect();
    }
}
