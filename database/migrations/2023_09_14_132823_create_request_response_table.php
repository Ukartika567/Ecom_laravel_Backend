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
        Schema::create('request_responses', function (Blueprint $table) {

            $table->id();
            
            $table->unsignedInteger('request_quote_id ')->nullable();
            $table->foreign('request_quote_id')->references('id')->on('requestquote')->onDelete('Cascade')->onUpdate('Cascade');
            $table->Integer('suplierid',11);

            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('Cascade')->onUpdate('Cascade');
            
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('Cascade')->onUpdate('Cascade');
            
            $table->unsignedBigInteger('subcategory_id');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('Cascade')->onUpdate('Cascade');
            
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('Cascade')->onUpdate('Cascade');
            
            $table->string('description',250)->nullable();
            $table->string('unit_of_measurement',100)->nullable();
            $table->string('whole_price_per_unit',100)->nullable();
            $table->string('min_order_qty',100)->nullable();
            $table->string('special_offer_deals',100)->nullable();
            $table->string('packaging_detail')->nullable();
            $table->string('shipp_methods')->nullable();
            $table->dateTime('estimated_days')->nullable();
            $table->string('requiredtime')->nullable();
            $table->enum('status',['New','InProgress','Approved','InShipping','Delivered'])->nullable();
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
        Schema::dropIfExists('request_responses');
    }
};
