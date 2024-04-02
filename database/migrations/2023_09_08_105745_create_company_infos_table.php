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
        Schema::create('company_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('companyname',45)->nullable();
            $table->string('contactpersonname',45)->nullable();
            $table->string('companyaddr')->nullable();
            $table->string('companyemail',45)->nullable();
            $table->integer('companyphone')->nullable();
            $table->string('industry',45)->nullable();
            $table->string('supporthour',45)->nullable();
            $table->string('supportadrr',100)->nullable();
            $table->string('businessname',100)->nullable();
            $table->string('businesstype',50)->nullable();
            $table->string('businessregnum',50)->nullable();
            $table->string('taxidentifynum',50)->nullable();
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
        Schema::dropIfExists('company_infos');
    }
};
