<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = fake()->unique()->name();
        $slug = Str::slug($title);    

        $subCategories = [9,10,11,12];
        $subCateRandKey = array_rand($subCategories);

        $brands = [1,3,7,8,9,10];
        $brandRandKey = array_rand($brands);

        return [
            'title' => $title,
            'slug' => $slug,
            'category_id' => 106,
            'sub_category_id' => $subCategories[$subCateRandKey],
            'brand_id' => $brands[$brandRandKey],
            'price' => rand(10,1000),
            'sku' => rand(1000,10000),  
            'track_qty' => 'Yes',
            'qty' => 10,
            'is_featured' => 'No',
            'status' => 1,
        ];
    }
}
