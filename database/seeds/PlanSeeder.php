<?php

use Illuminate\Database\Seeder;
use Osiset\ShopifyApp\Storage\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $Basic = new Plan();
        $Basic->name = config('shopifyApi.plans')[0];
        $Basic->type = 'RECURRING';
        $Basic->price = 9.99;
        $Basic->capped_amount = 0.00;
        $Basic->terms = "Alpha Feed Basic Plan Terms And Conditions.";
        $Basic->trial_days = 7;
        $Basic->on_install = 0;
        $Basic->save();

        $Small = new Plan();
        $Small->name = config('shopifyApi.plans')[1];
        $Small->type = 'RECURRING';
        $Small->price = 19.99;
        $Small->capped_amount = 0.00;
        $Small->terms = "Alpha Feed Small Plan Terms And Conditions.";
        $Small->trial_days = 7;
        $Small->on_install = 1;
        $Small->save();

        $Medium = new Plan();
        $Medium->name = config('shopifyApi.plans')[2];
        $Medium->type = 'RECURRING';
        $Medium->price = 29.99;
        $Medium->capped_amount = 0.00;
        $Medium->terms = "Alpha Feed Medium Plan Terms And Conditions.";
        $Medium->trial_days = 7;
        $Medium->on_install = 1;
        $Medium->save();

        $ALL_IN = new Plan();
        $ALL_IN->name = config('shopifyApi.plans')[3];
        $ALL_IN->type = 'RECURRING';
        $ALL_IN->price = 49.99;
        $ALL_IN->capped_amount = 0.00;
        $ALL_IN->terms = "Alpha Feed ALL-IN Plan Terms And Conditions.";
        $ALL_IN->trial_days = 7;
        $ALL_IN->on_install = 1;
        $ALL_IN->save();
    }
}
