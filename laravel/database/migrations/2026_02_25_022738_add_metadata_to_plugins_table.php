<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->string('version')->nullable()->after('provider');
            $table->text('description')->nullable()->after('version');
            $table->string('author')->nullable()->after('description');
            $table->json('requires')->nullable()->after('author');
        });
    }

    public function down()
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['version', 'description', 'author', 'requires']);
        });
    }
};
