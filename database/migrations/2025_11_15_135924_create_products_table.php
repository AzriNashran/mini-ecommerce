<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->foreignId('category_id')->constrained()->cascadeOnDelete();
      $table->decimal('price', 12, 2);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('products');
  }
};
