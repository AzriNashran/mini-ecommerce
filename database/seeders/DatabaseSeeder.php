<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Customer, Category, Product, Order, OrderItem};
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $categories = collect(['Electronics','Accessories','Home','Books','Toys'])
            ->map(fn($name) => Category::create(['name' => $name]));

        $products = collect(range(1,20))->map(function($i) use ($categories){
            return Product::create([
                'name' => 'Product '.$i,
                'category_id' => $categories->random()->id,
                'price' => rand(1000, 20000) / 100
            ]);
        });

        $states = ['Selangor','Kuala Lumpur','Johor','Penang','Sabah'];
        $customers = collect(range(1,15))->map(function($i) use ($states) {
            return Customer::create([
                'name' => 'Customer '.$i,
                'email' => 'customer'.$i.'@example.com',
                'state' => $states[array_rand($states)]
            ]);
        });

        foreach (range(1,60) as $i) {
            $customer = $customers->random();
            $orderDate = Carbon::now()->subDays(rand(0, 120));
            $order = Order::create([
                'customer_id' => $customer->id,
                'order_date' => $orderDate->toDateString(),
                'total_amount' => 0
            ]);

            $itemsCount = rand(1,4);
            $total = 0;
            for ($j=0; $j<$itemsCount; $j++) {
                $product = $products->random();
                $quantity = rand(1,5);
                $unitPrice = $product->price;
                $subtotal = $quantity * $unitPrice;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice
                ]);
                $total += $subtotal;
            }

            $order->update(['total_amount' => $total]);
        }
    }
}
