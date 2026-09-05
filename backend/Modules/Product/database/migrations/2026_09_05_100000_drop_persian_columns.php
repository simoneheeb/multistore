<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the Persian half of every catalogue record.
 *
 * The site was originally bilingual: each entity carried an English `name`
 * plus a Persian `name_fa`, rendered as a two-line heading. The site is now
 * English only, so the `*_fa` columns have no reader.
 *
 * Before dropping, any row whose English field is empty is backfilled from
 * its Persian counterpart - otherwise a record that had only been filled in
 * on the Persian side would silently lose its name.
 *
 * Lives in the Product module because it spans all three catalogue tables and
 * products are the table the other two exist to serve.
 */
return new class extends Migration
{
    /**
     * table => [english column => persian column]
     */
    protected array $map = [
        'products' => ['name' => 'name_fa', 'description' => 'description_fa'],
        'categories' => ['name' => 'name_fa', 'description' => 'description_fa'],
        'brands' => ['name' => 'name_fa', 'description' => 'description_fa'],
    ];

    public function up(): void
    {
        foreach ($this->map as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            // Only the columns actually present are touched. On a fresh
            // install the create migrations never add them, so this whole
            // migration is a no-op; on an existing database it does the work.
            $present = [];

            foreach ($columns as $english => $persian) {
                if (! Schema::hasColumn($table, $persian)) {
                    continue;
                }

                $present[] = $persian;

                // Salvage content that only exists on the Persian side.
                DB::table($table)
                    ->where(function ($query) use ($english) {
                        $query->whereNull($english)->orWhere($english, '');
                    })
                    ->whereNotNull($persian)
                    ->update([$english => DB::raw($persian)]);
            }

            if ($present === []) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($present) {
                $blueprint->dropColumn($present);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->map as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $english => $persian) {
                    if (Schema::hasColumn($table, $persian)) {
                        continue;
                    }

                    // Recreated nullable: the original content is gone, and a
                    // NOT NULL column would fail on any existing row.
                    str_contains($persian, 'description')
                        ? $blueprint->text($persian)->nullable()
                        : $blueprint->string($persian)->nullable();
                }
            });
        }
    }
};
