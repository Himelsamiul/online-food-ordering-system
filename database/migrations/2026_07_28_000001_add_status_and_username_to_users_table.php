<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Admin accounts can now be switched off, and carry a username.
     *
     * The username is what a locked-out admin types on the password-assistance
     * form so the superadmin can sanity-check the request against the account.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->string('deactivation_reason')->nullable()->after('deactivated_at');
            $table->timestamp('password_changed_at')->nullable()->after('deactivation_reason');

            $table->index('is_active');
        });

        // Backfill a username for every existing account from the email local
        // part, de-duplicated, so the unique index never bites on legacy rows.
        $seen = [];

        foreach (DB::table('users')->select('id', 'email')->get() as $user) {
            $base = Str::slug(Str::before($user->email, '@'), '') ?: 'user';
            $name = $base;
            $n    = 1;

            while (isset($seen[$name]) || DB::table('users')->where('username', $name)->exists()) {
                $name = $base . (++$n);
            }

            $seen[$name] = true;

            DB::table('users')->where('id', $user->id)->update(['username' => $name]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'username', 'is_active', 'deactivated_at',
                'deactivation_reason', 'password_changed_at',
            ]);
        });
    }
};
