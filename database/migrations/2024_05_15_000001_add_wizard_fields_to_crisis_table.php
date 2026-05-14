<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Expand the crisis_type enum to also include 'medical'
        //    (kept 'illness' for backward compat — wizard uses 'medical')
        DB::statement("
            ALTER TABLE crisis MODIFY crisis_type
            ENUM('death','accident','illness','medical','natural_disaster','family_emergency')
            NOT NULL
        ");

        // 2) Add new wizard columns
        Schema::table('crisis', function (Blueprint $table) {
            $table->string('sub_category', 100)
                  ->nullable()
                  ->after('crisis_type')
                  ->comment('Malaysia Bencana classification sub-category (e.g. flood, landslide, road_accident)');

            $table->decimal('latitude', 10, 7)
                  ->nullable()
                  ->after('location')
                  ->comment('GPS latitude from HTML5 geolocation');

            $table->decimal('longitude', 10, 7)
                  ->nullable()
                  ->after('latitude')
                  ->comment('GPS longitude from HTML5 geolocation');

            $table->timestamp('incident_at')
                  ->nullable()
                  ->after('date_reported')
                  ->comment('When the incident occurred (separate from date_reported)');

            // Index for filtering by sub_category in admin views
            $table->index('sub_category');
        });
    }

    public function down(): void
    {
        Schema::table('crisis', function (Blueprint $table) {
            $table->dropIndex(['sub_category']);
            $table->dropColumn(['sub_category', 'latitude', 'longitude', 'incident_at']);
        });

        // Revert the enum to its original set
        DB::statement("
            ALTER TABLE crisis MODIFY crisis_type
            ENUM('death','accident','illness','natural_disaster','family_emergency')
            NOT NULL
        ");
    }
};
