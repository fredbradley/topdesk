<?php

namespace FredBradley\TOPDesk\Models;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseModel
{
    private static string $endpoint;

    private static string $model;

    public function __construct(array|object $object)
    {
        if (is_array($object)) {
            $object = (object) $object;
        }
        foreach (get_object_vars($object) as $key => $value) {
            $this->$key = $object->$key;
        }
    }

    public static function find(string $id, bool $forgetCache = false): BaseModel
    {
        return self::findById($id, $forgetCache);
    }

    private static function setEndpointAndModel(): void
    {
        self::$model = get_called_class();
        self::$endpoint = match (self::$model) {
            Asset::class => 'assetmgmt/assets',
            Operator::class => 'operators',
            OperatorGroup::class => 'operatorgroups',
            Person::class => 'persons',
            PersonGroup::class => 'persongroups'
        };
    }

    public static function findFirstByVariable(
        string $variableKey,
        string $variableValue,
        bool $forgetCache = false
    ): BaseModel {
        return self::whereVariableEquals($variableKey, $variableValue, $forgetCache)->first();
    }

    public static function whereVariableEquals(
        string $variableKey,
        string $variableValue,
        bool $forgetCache = false
    ): Collection {
        self::setEndpointAndModel();

        $cacheKey = TOPDesk::setupCacheObject(cacheKey: Str::slug(self::$endpoint.$variableKey.$variableValue),
            forgetCache: $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($variableValue, $variableKey) {
            $result = TOPDesk::query()->get('api/'.self::$endpoint.'/', [
                'query' => $variableKey.'=='.$variableValue,
            ])->throw()->collect()->mapInto(self::$model);

            return $result;
        });
    }

    public static function findById(string $id, bool $forgetCache = false): BaseModel
    {
        self::setEndpointAndModel();

        $cacheKey = TOPDesk::setupCacheObject(cacheKey: Str::slug(self::$endpoint.'id'.$id), forgetCache: $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($id) {
            $endpoint = 'api/'.self::$endpoint.'/id/'.$id;
            if (self::$model === Asset::class) {
                $endpoint = 'api/'.self::$endpoint.'/'.$id;
            }

            $result = TOPDesk::query()->get($endpoint)->throw()->object();

            return new self::$model($result);
        });
    }
}
