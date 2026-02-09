<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Order;
use App\Models\CafeTable;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'order_number' => 'ORD-' . Str::upper(Str::random(10)),
            // ATAU: 'ORD-' . Str::uuid(),

            'cafe_table_id' => \App\Models\CafeTable::inRandomOrder()->value('id'),

            'status' => $this->faker->randomElement([
                'pending',
                'confirmed',
                'processing',
                'completed',
            ]),

            'payment_status' => $this->faker->randomElement(['unpaid', 'paid']),
            'payment_method' => $this->faker->randomElement(['cash', 'transfer']),
            'total_price' => $this->faker->numberBetween(20000, 300000),

            'created_at' => $this->faker->dateTimeBetween('-7 days'),
            'updated_at' => now(),
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (Order $order) {

            $menus = Menu::inRandomOrder()->take(rand(1, 5))->get();

            $total = 0;

            foreach ($menus as $menu) {
                $qty = rand(1, 3);
                $price = $menu->price;
                $subtotal = $qty * $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id'  => $menu->id,
                    'qty'      => $qty,
                    'price'    => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // update total_price di orders
            $order->update([
                'total_price' => $total,
            ]);
        });
    }
}
