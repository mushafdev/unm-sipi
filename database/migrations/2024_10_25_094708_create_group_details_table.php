<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('mahasiswa_id')->nullable();
            $table->enum('verify',['N','Y']);
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onUpdate('cascade')->onDelete('restrict');
            $table->dateTime('verify_at',$precision = 0)->nullable();

            $table->unsignedBigInteger('pembekalan_id')->nullable();
            $table->enum('pembekalan_verify',['N','Y']);
            $table->dateTime('pembekalan_verify_at',$precision = 0)->nullable();
            $table->unsignedBigInteger('pembekalan_verify_by',$precision = 0)->nullable();
            $table->string('pembekalan_sertifikat')->nullable();
            $table->foreign('pembekalan_id')->references('id')->on('pembekalans')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('pembekalan_verify_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_details');
    }
};
