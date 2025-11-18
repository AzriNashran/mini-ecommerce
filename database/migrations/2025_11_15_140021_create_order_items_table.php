<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('order_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('order_id')->constrained()->cascadeOnDelete();
      $table->foreignId('product_id')->constrained()->cascadeOnDelete();
      $table->integer('quantity')->unsigned();
      $table->decimal('unit_price', 12, 2);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('order_items');
  }
};
