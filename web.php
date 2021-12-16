<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/*
Route::get('/phpinfo', function () {
    echo "<pre>";
    echo phpinfo();
    exit();
});

Route::get('/', function () {
    $url = env('APP_URL');
    return redirect()->away($url.'website/');
});
*/

//Route::get('/', 'HomeController@index');// Redirect to wordpress site

//Clear Config cache:
Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('config:clear');
    //$exitCode = Artisan::call('cache:clear');
   // $exitCode = Artisan::call('config:cache');
    //$exitCode = Artisan::call('route:clear');
    
    return '<h1>Clear Config cleared...</h1>';
    // return what you want
});



Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', function () {
    return view('welcome');
});
/* Front Section Routes */
Route::namespace('Front')->group(function () {
    Route::prefix('customer')->as('customer.')->group(function() {
        Route::get('renewSubscription/{customer}', 'CustomerController@renewSubscription')->name('renewSubscription')->middleware('auth:custom_email');
        Route::post('renewSubscription', 'CustomerController@renewSubscriptionStore')->name('renewSubscriptionStore');
        // CODE FOR DEBugging the stripe payment

        //START CODE FOR APPLE PAY AND GOOGLE PAY 17-11-2021
        Route::get('applesubscription/{customer}', 'AppleSubscriptionController@applesubscription')->name('applesubscription')->middleware('auth:custom_email');
        Route::post('applesubscription', 'AppleSubscriptionController@applesubscriptionStore')->name('applesubscriptionStore');
        Route::post('createpaymentintent', 'AppleSubscriptionController@applesubscriptionIntent')->name('applesubscriptionIntent');
        Route::get('applepaymentDone/', 'AppleSubscriptionController@applepaymentDone')->name('applepaymentDone');
        //END CODE FOR APPLE PAY AND GOOGLE PAY
        
        Route::get('subscription/{customer}', 'SubscriptionController@subscription')->name('subscription')->middleware('auth:custom_email');
        Route::post('subscription', 'SubscriptionController@subscriptionStore')->name('subscriptionStore');
        Route::post('onetimeSubscription', 'SubscriptionController@onetimeSubscriptionStore')->name('onetimeSubscriptionStore');
        Route::get('paymentDone/', 'SubscriptionController@paymentDone')->name('paymentDone');

        Route::get('subscriptions/cancelperiod/{subscription}','SubscriptionController@cancelforperiod')->name('cancelSubPeriod');// for cancel subscription
    
        Route::namespace('Auth\Password')->group(function() {
           // Route::get('forgotPassword', 'ForgotPasswordController@showLinkRequestForm')->name('forgotPassword');
            //Route::post('forgotPasswordEmail', 'ForgotPasswordController@sendResetLinkEmail')->name('forgotPasswordEmail');
            Route::get('resetPassword/{token}', 'ResetPasswordController@showResetForm')->name('resetPassword');
            Route::post('resetPasswordEmail', 'ResetPasswordController@reset')->name('resetPasswordEmail');
            Route::get('resetPasswordDone/', 'ResetPasswordController@resetPasswordDone')->name('resetPasswordDone');
        }); 
        Route::namespace('Auth\Login')->group(function() {
           // Route::get('login', 'CustomerController@showLoginForm')->name('login');
           // Route::post('login', 'CustomerController@login')->name('login');
            Route::post('logout', 'CustomerController@logout')->name('logout');
        });
    
    });
});


Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');



/* Admin Section Routes */
Route::namespace('Admin')->group(function () {
    Route::prefix('jd-admin')->group(function () {
        Route::group(['middleware' => 'prevent-back-history'],function(){
            /* Login & Logout Section */
            Route::get('/', 'AdminSectionsController@index')->name('jdAdmin');
            Route::post('/adminLogout', 'AdminSectionsController@adminLogout')->name('adminLogout');
        });
       
        Route::group(array('middleware' => ['auth', 'prevent-back-history']), function () {

            /* Admin Dashboard Section */
            Route::get('/dashboard', 'AdminSectionsController@dashboard')->name('dashboard');
            /* Profile Section */
            Route::get('/profile', 'AdminSectionsController@profile')->name('profile');
            Route::post('/profile-update', 'AdminSectionsController@profileUpdate')->name('profileUpdate');

            /* Customers (Vendor and User) Section */
            Route::resource('customers', 'CustomerController');
            Route::get('changeStatus/{customer}', 'CustomerController@changeStatus')->name('customers.changeStatus');
            Route::get('customer-list/{customer_type}', 'CustomerController@customerTypeCount')->name('customers.customerTypeCount');
            Route::post('customerReportSubmit', 'CustomerController@customerReportSubmit')->name('customers.customerReportSubmit');
            Route::post('cutomerReportCSV', 'CustomerController@cutomerReportCSV')->name('customers.cutomerReportCSV');
  

            /* Service Type Section */
            Route::resource('servicetype', 'ServiceTypeController');
            Route::get('changeServiceStatus/{service}', 'ServiceTypeController@changeStatus')->name('servicetype.changeStatus');

            /* Report Section */ 
			Route::get('errandReport', 'ReportController@errandReport')->name('reports.errandReport');
            Route::post('errandReportSubmit', 'ReportController@errandReportSubmit')->name('reports.errandReportSubmit');
            Route::post('errandReportCSV', 'ReportController@errandReportCSV')->name('reports.errandReportCSV');
            
            Route::get('vendorerrandReport', 'ReportController@vendorerrandReport')->name('reports.vendorerrandReport');
            Route::post('vendorerrandReportSubmit', 'ReportController@vendorerrandReportSubmit')->name('reports.vendorerrandReportSubmit');
            Route::post('vendorerrandReportCSV', 'ReportController@vendorerrandReportCSV')->name('reports.vendorerrandReportCSV');
            /* Subscription Plan Section */
            Route::resource('subscriptionplans', 'SubscriptionPlanController');
            /* Subscription Section */
            Route::get('subscriptions', 'SubscriptionController@index')->name('subscriptions.index');
            Route::get('subscriptionPaymentHistory/{customerId}', 'SubscriptionController@paymentHistory')->name('subscriptions.paymentHistory');
          
           


        });
    }); 
});