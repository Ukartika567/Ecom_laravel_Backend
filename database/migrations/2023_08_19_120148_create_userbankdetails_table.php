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
        Schema::create('userbankdetails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->integer('micr_code')->nullable();
            $table->bigInteger('ifsc_code')->nullable();
            $table->string('account_type')->nullable();
            $table->bigInteger('account_number')->nullable();
            $table->integer('account_balance')->nullable();
            $table->string('fd_link')->nullable();
            $table->string('nominee')->nullable();
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
        Schema::dropIfExists('userbankdetails');
    }
};
