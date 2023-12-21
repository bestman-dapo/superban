<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('superbanControlTable', function (Blueprint $table) {
            $table->id();
            $table->string('user_identifier');
            $table->enum('type', ['userid', 'ipaddress', 'useremail']);
            $table->text('reqUrl');
            $table->integer('requestCount');
            $table->dateTime('initialRequestTime')->nullable();
            $table->dateTime('lastSuccessTime')->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superbanControlTable');
    }
};
