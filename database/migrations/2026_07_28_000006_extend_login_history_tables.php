<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Both login histories gain the parsed client columns.
     *
     * They are stored rather than derived from user_agent at render time so the
     * lists can be filtered and exported on them without parsing every row.
     * session_id links a history row to its audit entries.
     */
    public function up(): void
    {
        Schema::table('admin_login_histories', function (Blueprint $table) {
            $table->string('browser')->nullable()->after('user_agent');
            $table->string('device')->nullable()->after('browser');
            $table->string('platform')->nullable()->after('device');
            $table->string('session_id')->nullable()->after('platform');
            $table->string('logout_type', 20)->nullable()->after('logged_out_at'); // manual | expired
            $table->string('failure_reason')->nullable()->after('successful');

            $table->index('session_id');
            $table->index('successful');
        });

        Schema::table('login_histories', function (Blueprint $table) {
            $table->string('browser')->nullable()->after('user_agent');
            $table->string('device')->nullable()->after('browser');
            $table->string('platform')->nullable()->after('device');
            $table->string('session_id')->nullable()->after('platform');
            $table->boolean('successful')->default(true)->after('session_id');

            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('admin_login_histories', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropIndex(['successful']);
            $table->dropColumn(['browser', 'device', 'platform', 'session_id', 'logout_type', 'failure_reason']);
        });

        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropIndex(['logged_in_at']);
            $table->dropColumn(['browser', 'device', 'platform', 'session_id', 'successful']);
        });
    }
};
