<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;

trait Branches
{
    /**
     * @param  array  $query  Supports FIQL via 'query' key and field selection via '$fields'
     */
    public function getBranches(array $query = []): Collection
    {
        return self::query()->get('api/branches', $query)->throw()->collect();
    }

    public function getBranch(string $id): object
    {
        return $this->get('api/branches/id/'.$id);
    }

    /**
     * @param  array  $data  Keys: name, specification, clientReferenceNumber, timeZone, extraA, extraB,
     *                       phone, fax, email, website, branchType, headBranch, address, postalAddress, etc.
     */
    public function createBranch(array $data): object
    {
        return $this->post('api/branches', $data);
    }

    public function updateBranch(string $id, array $data): object
    {
        return $this->patch('api/branches/id/'.$id, $data);
    }

    public function archiveBranch(string $id): array|object
    {
        return $this->patch('api/branches/id/'.$id.'/archive');
    }

    public function unarchiveBranch(string $id): array|object
    {
        return $this->patch('api/branches/id/'.$id.'/unarchive');
    }

    /**
     * @param  array  $options  Keys: $top (max 10000), name (prefix filter), archived
     */
    public function getBranchLookup(array $options = []): Collection
    {
        return self::query()->get('api/branches/lookup', $options)->throw()->collect();
    }

    public function getBranchId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('branch_id_'.md5($name), $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::hours(1), function () use ($name) {
            return self::query()->get('api/branches/lookup', ['name' => $name])->throw()->collect()->first()['id'];
        });
    }

    public function getBranchDesignations(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('branch_designations', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/branches/designations')->throw()->collect());
    }

    public function getBranchBuildingLevels(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('branch_building_levels', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/branches/buildingLevels')->throw()->collect());
    }

    public function getBranchAttachments(string $id, array $options = []): Collection
    {
        return self::query()->get('api/branches/id/'.$id.'/attachments', $options)->throw()->collect();
    }

    public function deleteBranchAttachment(string $id, string $attachmentId): array|object
    {
        return $this->delete('api/branches/id/'.$id.'/attachments/'.$attachmentId);
    }
}
