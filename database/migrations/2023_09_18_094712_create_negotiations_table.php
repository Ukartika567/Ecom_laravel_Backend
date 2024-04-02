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
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('request_id')->nullable();
            $table->foreign('request_id')->references('id')->on('requestquote')->onDelete('Cascade')->onUpdate('Cascade');
            $table->unsignedInteger('response_id')->nullable();
            $table->foreign('response_id')->references('id')->on('request_response')->onDelete('Cascade')->onUpdate('Cascade');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('Cascade')->onUpdate('Cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('Cascade')->onUpdate('Cascade');
            $table->string('quantity',45)->nullable();
            $table->string('unit_price',45)->nullable();
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
        Schema::dropIfExists('negotiations');
    }
};
