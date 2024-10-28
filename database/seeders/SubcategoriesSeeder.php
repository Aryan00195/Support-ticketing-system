<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subcategory;
class SubcategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $subcategories = [
            ['category_id' => 1, 'name' => 'How to Contact Support'],
            
            
            ['category_id' => 2, 'name' => 'Payment Methods Question'],
          
            
            ['category_id' => 3, 'name' => 'Software Installation Help'],
           
            
            ['category_id' => 4, 'name' => 'Login Problems'],
           
            ['category_id' => 5, 'name' => 'Feature Enhancement Suggestion'],
          
            ['category_id' => 6, 'name' => 'New Feature Proposal'],
           
            ['category_id' => 7, 'name' => 'Error Message Explanation Request'],
          

            ['category_id' => 8, 'name' => 'Account Registration Assistance'],
          


            ['category_id' => 9, 'name' => 'Account Security Audit Request'],
          
            ['category_id' => 10, 'name' => 'Access Permission Request'],
           


            ['category_id' => 11, 'name' => 'Subscription Renewal Request'],
           

            ['category_id' => 12, 'name' => 'Order Tracking Assistance'],
          

            ['category_id' => 13, 'name' => 'Shipping Address Update Request'],
           

            ['category_id' => 14, 'name' => 'Refund Status Inquiry'],
          

            ['category_id' => 15, 'name' => 'Product Specifications Inquiry'],
           

            ['category_id' => 16, 'name' => 'Documentation Access Assistance'],
            

            ['category_id' => 17, 'name' => 'API Integration Assistance'],
           

            ['category_id' => 18, 'name' => 'Integration Setup Help'],
        

            ['category_id' => 19, 'name' => 'Data Transfer Assistance'],
          
            ['category_id' => 20, 'name' => 'Customization Inquiry'],
            

        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create($subcategory);
        }
    }
}
