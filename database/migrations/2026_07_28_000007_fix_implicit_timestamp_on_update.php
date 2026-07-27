<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Strip MySQL's implicit ON UPDATE CURRENT_TIMESTAMP.
     *
     * With explicit_defaults_for_timestamp off (the default on this stack), the
     * FIRST non-nullable TIMESTAMP column in a table is silently created as
     *
     *     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
     *
     * so ANY update to the row rewrites it to now. Two live consequences:
     *
     *   otp_codes.expires_at        — counting a wrong guess reset the expiry to
     *                                 the current instant, so the very next check
     *                                 reported the code as expired. Every OTP was
     *                                 effectively single-attempt.
     *   login_histories.logged_in_at— stamping logged_out_at rewrote the login
     *                                 time, so every customer session duration
     *                                 computed as zero.
     *
     * Making the columns nullable removes both the implicit default and the
     * implicit ON UPDATE. Raw SQL rather than ->change() so the resulting
     * definition is unambiguous.
     */
    private const COLUMNS = [
        'otp_codes'           => 'expires_at',
        'password_reset_links' => 'expires_at',
        'login_histories'     => 'logged_in_at',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::COLUMNS as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TIMESTAMP NULL DEFAULT NULL");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::COLUMNS as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TIMESTAMP NOT NULL");
            }
        }
    }
};
