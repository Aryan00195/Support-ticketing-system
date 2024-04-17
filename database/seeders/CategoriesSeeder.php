<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categories = [
            ['name' => 'General Inquiry'],
            ['name' => 'Billing/Payments'],
            ['name' => 'Technical Question'],
            ['name' => 'Account Issues'],
            ['name' => 'Product Feedback'],
            ['name' => 'Feature Request'],
            ['name' => 'Reporting a Bug'],
            ['name' => 'Account Setup'],
            ['name' => 'Security Concerns'],
            ['name' => 'User Access'],
            ['name' => 'Subscription Management'],
            ['name' => 'Order Status'],
            ['name' => 'Delivery/Shipping'],
            ['name' => 'Refunds/Returns'],
            ['name' => 'Product Information'],
            ['name' => 'Documentation Help'],
            ['name' => 'API Support'],
            ['name' => 'Integration Assistance'],
            ['name' => 'Data Migration'],
            ['name' => 'Customization Requests'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
