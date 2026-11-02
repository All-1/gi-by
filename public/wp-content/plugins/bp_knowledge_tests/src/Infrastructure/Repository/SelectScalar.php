<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

/**
 * DBWorker::selectUni_2 with a single column may return a scalar, stdClass, or camelCase array.
 */
final class SelectScalar
{
    public static function intId(mixed $result, string $field = 'id'): ?int
    {
        if (is_int($result)) {
            return $result > 0 ? $result : null;
        }
        if (is_string($result) && ctype_digit($result)) {
            $id = (int) $result;

            return $id > 0 ? $id : null;
        }
        if (is_object($result) && isset($result->$field)) {
            $id = (int) $result->$field;

            return $id > 0 ? $id : null;
        }
        if (is_array($result)) {
            $camel = lcfirst(str_replace('_', '', ucwords($field, '_')));
            if (isset($result[$field])) {
                $id = (int) $result[$field];

                return $id > 0 ? $id : null;
            }
            if (isset($result[$camel])) {
                $id = (int) $result[$camel];

                return $id > 0 ? $id : null;
            }
        }

        return null;
    }
}
