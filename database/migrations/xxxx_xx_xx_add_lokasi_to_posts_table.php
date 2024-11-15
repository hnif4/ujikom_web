<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('posts', 'lokasi')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('lokasi')->nullable()->after('isi');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('posts', 'lokasi')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('lokasi');
            });
        }
    }
}; 