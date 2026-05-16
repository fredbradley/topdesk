<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait IncidentLookups
{
    public function getCallTypes(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_call_types', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/call_types')->throw()->collect());
    }

    public function getDurations(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_durations', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/durations')->throw()->collect());
    }

    public function getEntryTypes(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_entry_types', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/entry_types')->throw()->collect());
    }

    public function getImpacts(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_impacts', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/impacts')->throw()->collect());
    }

    public function getPriorities(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_priorities', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/priorities')->throw()->collect());
    }

    public function getUrgencies(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_urgencies', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/urgencies')->throw()->collect());
    }

    public function getClosureCodes(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_closure_codes', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/closure_codes')->throw()->collect());
    }

    public function getEscalationReasons(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_escalation_reasons', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/escalation-reasons')->throw()->collect());
    }

    public function getDeescalationReasons(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('incident_deescalation_reasons', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/incidents/deescalation-reasons')->throw()->collect());
    }

    public function getTimeSpentReasons(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('timespent_reasons', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), fn () => self::query()->get('api/timespent-reasons')->throw()->collect());
    }

    /**
     * @param  array  $options  Filters: incident, contract, person, service, branch, budgetHolder, department, etc.
     */
    public function getSlas(array $options = []): Collection
    {
        return self::query()->get('api/incidents/slas', $options)->throw()->collect();
    }

    public function getSlaServices(array $options = []): Collection
    {
        return self::query()->get('api/incidents/slas/services', $options)->throw()->collect();
    }

    public function getCategories(array $options = []): Collection
    {
        return self::query()->get('api/categories', $options)->throw()->collect();
    }
}
