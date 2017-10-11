<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDonationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->string('donor_address')->nullable()->after('user_id');
            $table->string('donor_phone')->nullable()->after('user_id');
            $table->string('donor_email')->nullable()->after('user_id');
            $table->string('donor_name')->nullable()->after('user_id');
            $table->integer('recipient_id')->unsigned()->nullable()->after('user_id');
            $table->string('note')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->integer('user_id')->change();
            $table->dropColumn('recipient_id');
            $table->dropColumn('donor_name');
            $table->dropColumn('donor_email');
            $table->dropColumn('donor_phone');
            $table->dropColumn('donor_address');
            $table->dropColumn('note');
        });
    }
}
