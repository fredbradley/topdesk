<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait Suppliers
{
    /**
     * @param  array  $query  Keys: start, page_size, query (FIQL)
     */
    public function getSuppliers(array $query = []): Collection
    {
        return self::query()->get('api/suppliers', $query)->throw()->collect();
    }

    public function getSupplier(string $id): object
    {
        return $this->get('api/suppliers/'.$id);
    }

    /**
     * @param  array  $options  Keys: $top, name (prefix filter), archived
     */
    public function getSupplierLookup(array $options = [], bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('supplier_lookup', $forgetCache);

        return self::cache()->remember($cacheKey, EasySeconds::hours(1), fn () => self::query()->get('api/suppliers/lookup', $options)->throw()->collect());
    }

    /**
     * @param  array  $query  Keys: start, page_size
     */
    public function getSupplierContacts(array $query = []): Collection
    {
        return self::query()->get('api/supplierContacts', $query)->throw()->collect();
    }

    public function getSupplierContact(string $id): object
    {
        return $this->get('api/supplierContacts/'.$id);
    }
}
