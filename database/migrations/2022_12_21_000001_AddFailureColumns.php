<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFailureColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

       
        Schema::table("transact_transactions", function(Blueprint $table) {
            $table->boolean('failed')->index()->default(0)->after('paid_at');
            $table->string('failure_reason')->nullable()->after('failed');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("transact_transactions", function(Blueprint $table) {
            $table->dropColumn('failed');
            $table->dropColumn('failure_reason');
        });
       
    }
}
