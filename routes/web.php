<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth.shopify'])->group(function()
{

    Route::middleware(['CustomBillable'])->group(function()
    {

        Route::middleware(['SetupCheckTrue'])->group(function()
        {

            Route::get('/','WelcomeController@index')->name('home');
            Route::get('/productSearch/{query}/{cursor?}/{type?}','WelcomeController@productSearch')->name('productSearch');
            Route::get('/product/details/{id}','WelcomeController@getProductDetails')->name('productDetails');
            Route::get('/getVarients/{id}/{tag?}','WelcomeController@getProductVariants')->name('getVarients');
            Route::post('product/update', 'WelcomeController@update')->name('update');
            Route::post('Pproduct/update', 'WelcomeController@Pupdate')->name('updateProduct');
            Route::get('Pproduct/delete/{id?}', 'WelcomeController@Pdelete')->name('deleteProduct');
            Route::get('settings/update', 'WelcomeController@updateSettings')->name('updateSettings');
            Route::post('contact','ContactUsController@store')->name('contact.store');
            Route::post('setup/syncNow', 'WelcomeController@syncNow')->name('syncNow');
            Route::get('setup/SyncStatusNow', 'WelcomeController@SyncStatusNow')->name('SyncStatusNow');
            Route::get('dismissLimitAlert', 'WelcomeController@dismissLimitAlert')->name('dismissLimitAlert');
    
        });
        
        Route::middleware(['SetupCheckFalse'])->group(function () 
        {
    
            Route::get('setup','WelcomeController@setup')->name('setup');
            Route::get('getConnectionStatus', 'WelcomeController@getConnectionStatus')->name('getConnectionStatus');
            Route::get('getAcounts', 'WelcomeController@getAccounts')->name('getAcounts');
            Route::get('setup/google', 'SetupController@redirectToProvider')->name('redirect');
            Route::get('setup/google/callback', 'SetupController@handleProviderCallback')->name('callback');
            Route::get('setup/google/disconnect', 'SetupController@disconnect')->name('disconnect');
            Route::post('setup/account/connect', 'SetupController@accountConnect')->name('accountConnect');
            Route::get('setup/account/disconnect', 'SetupController@accountDisconnect')->name('accountDisconnect');
            Route::get('setup/domain/verify', 'SetupController@domainVerify')->name('domainVerify');
            Route::get('setup/domain/disconnect', 'SetupController@domainDisconnect')->name('disconnect.domain');
            Route::post('setup/sync', 'WelcomeController@sync')->name('sync');
            // Route::get('feed','WelcomeController@feed')->name('feed');
            // Route::post('/add','WelcomeController@addAcount')->name('addAcount');

        });

        Route::get('setup/product/category/search', 'WelcomeController@productCategorySearch')->name('product.category.search');
        
    });

    Route::get('plans','WelcomeController@plans')->name('plans');
    
});
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
// Route::get('google/feed','WelcomeController@feed')->name('googleFeed')->middleware('auth.proxy');
Route::get('test','WelcomeController@test')->name('test');
// Route::get('test2','WelcomeController@test2')->name('test2');