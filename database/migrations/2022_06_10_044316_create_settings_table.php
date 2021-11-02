<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('setup')->default(0);
            $table->boolean('enable')->default(0);
            $table->string('googleAccessToken')->nullable();
            $table->string('googleRefreshToken')->nullable();
            $table->string('googleAccountId')->nullable();
            $table->string('googleAccountEmail')->nullable();
            $table->string('merchantAccountId')->nullable();
            $table->string('merchantAccountName')->nullable();
            $table->string('country');
            $table->string('language');
            $table->string('currency');
            $table->enum('shipping',['auto','manual'])->default('auto');
            $table->enum('productIdFormat',['global','sku','variant'])->default('global');
            $table->enum('whichProducts',['all','collection'])->default('all');
            $table->enum('collectionType',['auto','custom'])->default('auto');
            $table->string('collectionsId')->nullable();
            $table->enum('productTitle',['default','seo'])->default('default');
            $table->enum('productdescription',['default','seo'])->default('default');
            $table->enum('variantSubmission',['first','all'])->default('all');
            $table->boolean('gtinSubmission')->default(0);
            $table->boolean('salePrice')->default(0);
            $table->boolean('secondImage')->default(0);
            $table->boolean('additionalImages')->default(0);
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->enum('ageGroup',['blank','newborn','infant','toddler','kids','adult'])->nullable();
            $table->enum('gender',['blank','male','female','unisex'])->nullable();
            $table->enum('productCondition',['blank','new','refurbished','used'])->nullable();
            $table->string('themeId');
            $table->string('domain');
            $table->text('store_name')->nullable();
            $table->text('store_email')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('country_name')->nullable();
            $table->string('plan_display_name')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamp('notification_date')->default(Carbon::now());
            $table->boolean('limit_notification')->default(0);
            $table->timestamps();
            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
