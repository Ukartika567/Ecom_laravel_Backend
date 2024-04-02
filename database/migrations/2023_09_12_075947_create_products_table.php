<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategory_id');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('Cascade')->onUpdate('Cascade');
            $table->string('name',45);
            $table->string('description',250)->nullable();
            $table->string('productImage')->nullable();
            $table->string('unit_of_measurement',100)->nullable();
            $table->string('whole_price_per_unit',100)->nullable();
            $table->string('min_order_qty',100)->nullable();
            $table->string('special_offer_deals',100)->nullable();
            $table->string('packaging_detail')->nullable();
            $table->string('shipp_methods')->nullable();
            $table->dateTime('estimated_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
