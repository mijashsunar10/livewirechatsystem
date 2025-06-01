<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('message_reactions', function (Blueprint $table) {
            //
            $table->renameColumn('chat_message_id', 'message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_reactions', function (Blueprint $table) {
            //
              $table->renameColumn('message_id', 'chat_message_id');
        });
    }
};
