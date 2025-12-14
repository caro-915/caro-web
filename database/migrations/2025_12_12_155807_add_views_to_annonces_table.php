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
    Schema::table('annonces', function (Blueprint $table) {
        $table->unsignedInteger('views')->default(0);
    });
}

public function down()
{
    Schema::table('annonces', function (Blueprint $table) {
        $table->dropColumn('views');
    });
}
};