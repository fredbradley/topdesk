<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Collection;

trait Departments
{
    /**
     * @param  array  $query  Keys: external_link_id, external_link_type
     */
    public function getDepartments(array $query = []): Collection
    {
        return self::query()->get('api/departments', $query)->throw()->collect();
    }

    public function createDepartment(string $name): object
    {
        return $this->post('api/departments', ['name' => $name]);
    }

    public function updateDepartment(string $id, string $name): object
    {
        return $this->patch('api/departments/id/'.$id, ['name' => $name]);
    }

    public function deleteDepartment(string $id): array|object
    {
        return $this->delete('api/departments/id/'.$id);
    }

    public function archiveDepartment(string $id): array|object
    {
        return $this->patch('api/departments/id/'.$id.'/archive');
    }

    public function unarchiveDepartment(string $id): array|object
    {
        return $this->patch('api/departments/id/'.$id.'/unarchive');
    }
}
