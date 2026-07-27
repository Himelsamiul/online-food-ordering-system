<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Full audit record.
     *
     * `changes` held the diff as {field: [old, new]}. Old and new are now
     * separate columns so they can be read, filtered and exported independently;
     * the model exposes a `changes` accessor that rebuilds the old shape, so
     * every existing view keeps working.
     *
     * The client columns are snapshots on purpose — an audit row must describe
     * the request as it happened, not as the session looks later.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_type', 32)->nullable()->after('user_role');   // admin | customer | system
            $table->string('module')->nullable()->after('event');              // Foods, Orders, Authentication…

            $table->json('old_values')->nullable()->after('changes');
            $table->json('new_values')->nullable()->after('old_values');

            $table->string('browser')->nullable()->after('ip_address');
            $table->string('device')->nullable()->after('browser');
            $table->string('platform')->nullable()->after('device');
            $table->text('user_agent')->nullable()->after('platform');
            $table->string('session_id')->nullable()->after('user_agent');
            $table->string('method', 10)->nullable()->after('url');

            $table->index('module');
            $table->index('user_type');
            $table->index('session_id');
        });

        // Split any existing {field: [old, new]} payloads into the new columns.
        foreach (DB::table('activity_logs')->whereNotNull('changes')->select('id', 'changes')->get() as $row) {
            $decoded = json_decode($row->changes, true);

            if (!is_array($decoded)) {
                continue;
            }

            $old = [];
            $new = [];

            foreach ($decoded as $field => $pair) {
                if (is_array($pair) && array_key_exists(0, $pair)) {
                    $old[$field] = $pair[0];
                    $new[$field] = $pair[1] ?? null;
                }
            }

            DB::table('activity_logs')->where('id', $row->id)->update([
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
            ]);
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn('changes');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->json('changes')->nullable()->after('description');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropIndex(['user_type']);
            $table->dropIndex(['session_id']);
            $table->dropColumn([
                'user_type', 'module', 'old_values', 'new_values',
                'browser', 'device', 'platform', 'user_agent', 'session_id', 'method',
            ]);
        });
    }
};
