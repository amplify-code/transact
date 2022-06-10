<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasTable('checkout_transactions')) {
            // if we've already got the checkout transaction table, just rename that 
            echo "checkout_transactions found - renaming.\n";
            Schema::rename('checkout_transactions', 'transact_transactions');

        } else {
            // otherewise, create a new table:
            Schema::create('transact_transactions', function(Blueprint $table) {
                $table->id();
                $table->string('transactable_type')->index();
                $table->integer('transactable_id')->index();
                $table->float('amount');
                $table->longtext('data');
                $table->timestamps();
            });

        }

        Schema::table("transact_transactions", function(Blueprint $table) {
            $table->string('uuid', 200)->index()->after('transactable_id');
            $table->string('provider')->index()->after('uuid');
            $table->boolean('is_recurring')->default(0)->after('provider');
            $table->timestamp('paid_at')->nullable()->index()->after('nett');
            $table->string('reference')->index()->after('paid_at');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        if(Schema::hasTable('checkout_orders')) {
            echo "checkout tables found - renaming transactions table to fit.\n";

            Schema::table("transact_transactions", function(Blueprint $table) {
                $table->dropColumn('provider');
                $table->dropColumn('uuid');
                $table->dropColumn('is_recurring');
                $table->dropColumn('paid_at');
                $table->dropColumn('reference');
            });

            Schema::rename('transact_transactions', 'checkout_transactions');
        } else {
            Schema::dropIfExists('transact_transactions');
        }
       
    }
}
