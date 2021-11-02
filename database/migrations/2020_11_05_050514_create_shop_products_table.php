<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->enum('ageGroup',['newborn','infant','toddler','kids','adult'])->nullable();
            $table->enum('gender',['male','female','unisex'])->nullable();
            $table->enum('productCondition',['new','refurbished','used'])->nullable();
            $table->string('productId');
            $table->text('title');
            $table->text('image');
            $table->string('status')->default('["Pending"]');
            $table->string('seoTitle')->nullable();
            $table->text('seoDescription')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_products');
    }
}
