<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username')->nullable();
            $table->string('email')->unique()->nullable();
            $table->bigInteger('phone_no')->nullable();
            $table->enum('gender',['male','female'])->default('male');
            $table->string('dob')->nullable();
            $table->string('hobbies')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->decimal('lat')->nullable();
            $table->decimal('long')->nullable();
            $table->decimal('distance')->nullable();
             $table->string('file')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('access_token',100)->nullable();
            $table->rememberToken();
            // $table->softDeletes();

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
        Schema::dropIfExists('users');
    }
}
