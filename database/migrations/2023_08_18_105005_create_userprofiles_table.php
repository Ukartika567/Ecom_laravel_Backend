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
        Schema::create('userprofiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('profile_picture')->nullable();
            $table->enum('gender',['M','F','O'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('mobile')->nullable()->unique();
            $table->unsignedBigInteger('category')->nullable();
            $table->foreign('category')->references('id')->on('categories')->onDelete('Cascade')->onUpdate('Cascade');
            $table->unsignedBigInteger('subcategory')->nullable();
            $table->foreign('subcategory')->references('id')->on('categories')->onDelete('Cascade')->onUpdate('Cascade');
            $table->string('business_name')->nullable();
            $table->string('industry')->nullable();
            $table->string('address')->nullable();
            $table->integer('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userprofiles');
    }
};
