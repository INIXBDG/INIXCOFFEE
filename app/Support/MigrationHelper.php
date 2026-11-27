<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationHelper
{
    public static function indexExists(string $table, string $indexName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(1) AS total FROM information_schema.statistics
                 WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $indexName]
            );

            return ((int) ($result->total ?? 0)) > 0;
        }

        return false;
    }

    public static function foreignKeyExists(string $table, string $constraintName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(1) AS total FROM information_schema.table_constraints
                 WHERE table_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = ?',
                [$table, $constraintName, 'FOREIGN KEY']
            );

            return ((int) ($result->total ?? 0)) > 0;
        }

        return false;
    }

    public static function foreignKeyExistsOnColumn(string $table, string $column, ?string $referencedTable = null): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $sql = 'SELECT COUNT(1) AS total FROM information_schema.key_column_usage
                    WHERE table_schema = DATABASE()
                      AND table_name = ?
                      AND column_name = ?
                      AND referenced_table_name IS NOT NULL';
            $params = [$table, $column];

            if ($referencedTable !== null) {
                $sql .= ' AND referenced_table_name = ?';
                $params[] = $referencedTable;
            }

            $result = DB::selectOne($sql, $params);

            return ((int) ($result->total ?? 0)) > 0;
        }

        return false;
    }

    public static function columnDataType(string $table, string $column): ?string
    {
        if (!Schema::hasTable($table)) {
            return null;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT LOWER(data_type) AS data_type FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
                [$table, $column]
            );

            return $result->data_type ?? null;
        }

        return null;
    }

    public static function addIndexIfNotExists(string $table, string|array $columns, ?string $indexName = null): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $colList = is_array($columns) ? $columns : [$columns];
        foreach ($colList as $col) {
            if (is_string($col)) {
                if (!Schema::hasColumn($table, $col)) {
                    return;
                }
            } elseif ($col instanceof Expression) {
                $raw = (string) $col->getValue(DB::connection()->getQueryGrammar());
                if (preg_match('/^`?([a-zA-Z0-9_]+)`?\s*\(/', trim($raw), $m) && !Schema::hasColumn($table, $m[1])) {
                    return;
                }
            }
        }

        $resolvedName = $indexName ?? ($table . '_' . implode('_', array_map(fn ($c) => is_string($c) ? $c : 'expr', $colList)) . '_index');
        if (self::indexExists($table, $resolvedName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            if ($indexName !== null) {
                $blueprint->index($columns, $indexName);
            } else {
                $blueprint->index($columns);
            }
        });
    }

    public static function addUniqueIfNotExists(string $table, string|array $columns, ?string $indexName = null): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $colList = is_array($columns) ? $columns : [$columns];
        foreach ($colList as $col) {
            if (is_string($col) && !Schema::hasColumn($table, $col)) {
                return;
            }
        }

        $resolvedName = $indexName ?? ($table . '_' . implode('_', $colList) . '_unique');
        if (self::indexExists($table, $resolvedName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            if ($indexName !== null) {
                $blueprint->unique($columns, $indexName);
            } else {
                $blueprint->unique($columns);
            }
        });
    }

    public static function dropIndexIfExists(string $table, string|array $index): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $indexName = is_array($index)
            ? ($table . '_' . implode('_', $index) . '_index')
            : $index;

        if (!self::indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    public static function dropUniqueIfExists(string $table, string|array $index): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $indexName = is_array($index)
            ? ($table . '_' . implode('_', $index) . '_unique')
            : $index;

        if (!self::indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropUnique($indexName);
        });
    }

    public static function dropForeignIfExists(string $table, string|array $foreign): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $constraintName = is_array($foreign)
            ? ($table . '_' . implode('_', $foreign) . '_foreign')
            : $foreign;

        if (!self::foreignKeyExists($table, $constraintName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($constraintName) {
            $blueprint->dropForeign($constraintName);
        });
    }
}
