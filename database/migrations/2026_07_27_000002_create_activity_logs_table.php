<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who did what in the admin panel.
     *
     * The actor's name and the subject's label are COPIED in, not joined.
     * The whole point of an audit trail is that it still reads correctly
     * after the admin or the record it describes has been deleted.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();   // admin who acted
            $table->string('user_name')->nullable();             // snapshot
            $table->string('user_role')->nullable();

            $table->string('event');            // created / updated / deleted / login / logout / custom
            $table->string('subject_type')->nullable();  // App\Models\Food
            $table->string('subject_label')->nullable(); // "Burger 1"
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('description');      // "deleted the food Burger 1"
            $table->json('changes')->nullable();// {field: [old, new]} for updates

            $table->string('ip_address', 64)->nullable();
            $table->string('url')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('event');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
