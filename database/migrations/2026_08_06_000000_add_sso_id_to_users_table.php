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
        Schema::table('users', function (Blueprint $table) {

            /**
             * Identifier unik dari SSO (mis. sub / id / uuid),
             * dipakai untuk memetakan akun SSO ke user lokal.
             */
            $table->string('sso_id')
                ->nullable()
                ->unique()
                ->after('id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('sso_id');

        });
    }
};
