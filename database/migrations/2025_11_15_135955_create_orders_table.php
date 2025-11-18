<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
      $table->date('order_date')->index();
      $table->decimal('total_amount', 12, 2);
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('orders');
  }
};
