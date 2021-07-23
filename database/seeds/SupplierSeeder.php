<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $factory->define(Supplier::class, function (Faker $faker) {
            return [
                'name' => $faker->name,
            ];
        });
    }
}
