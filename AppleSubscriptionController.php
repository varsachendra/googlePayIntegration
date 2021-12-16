<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Customer;
use App\Subscription;
use App\SubscriptionPlan;
use App\PaymentHistory;
use App\PaymentToken;
use App\StripeResponse;
use Illuminate\Support\Facades\Hash;
use Storage;
use Session;
use Auth;
use Mail;
use App\Mail\SubscriptionMail;
use Illuminate\Support\Arr;
use Stripe\Stripe;
use Stripe\Customer as CustomerStripe;
use Stripe\Charge;

class AppleSubscriptionController extends Controller
{

	protected $redirectTo = '/customer/applepaymentDone';// after payment redirect url

    
	public function applepaymentDone(){
        return view('front.customer.applepaymentDone');
    }
	
	public function applesubscription(Request $request,$plan_id)
    {
		
		// CODE FOR APPLE BUTTON
		\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
		\Stripe\ApplePayDomain::create([
			'domain_name' => 'punchbugg.com',
		]);
		
		$url_error = false;
		
		if(empty($request->uid) || empty($request->type) || empty($request->token)){
			$url_error = true;
			$request->session()->flash('danger', 'url somthing missing!');
		}

		$pay_token = PaymentToken::where('customer_id',$request->uid)->where('customer_type',$request->type)->first();
		if($pay_token && !empty($pay_token->token)){
			if(!empty($request->token) &&  ($pay_token->token != $request->token)){
				$url_error = true;
				$request->session()->flash('danger', 'Token miss match Please try again!');
			}
		}else {
			$url_error = true;
			$request->session()->flash('danger', 'Wrong url!');
		}

		// REMOVE ALL SET SEEION 
		$request->session()->forget('key');

		//$customer = Customer::findOrFail($customer_id);
		$customer = Auth::guard('custom_email')->user();
		Session::put('key', ['user_id' => $customer->id, 'email' => $customer->email, 'customer_type' => $customer->customer_type]);
		
		//$key = Session::get('key');
		//print_r($key);
		//exit;
		
		//Auth::loginUsingId($customer_id);
		$subscription_plan = $plan_id;//$_GET['plan_id'];

		//$customer = Auth::guard('customer')->user();
	
			//Check subscription is active or no
			/*
			$subscription = Subscription::where('customer_id', $customer->id)->get()->first();
			$expDate = date('Y-m-d', strtotime($subscription->expiration_date));
			$todayDate = date('Y-m-d');
			if ($subscription->status==1 && $todayDate<$expDate) {
				//return redirect(route('customer.profile', app()->getLocale()))->with('status', __('You subscription plan is currently active.'));
				return redirect()->back()->with('message', 'You subscription plan is currently active');
			}
			*/
		
			
			$intent = $customer->createSetupIntent();
			
			$subscriptionPlans = SubscriptionPlan::where(['status' => 1,'id'=>$subscription_plan])->get();
			
			
			$client_Intent ='';
			$mybrowser = getBrowser();
			if($mybrowser['name'] =="Chrome"){
				$subscription = SubscriptionPlan::where(['status' => 1,'id'=>$subscription_plan])->first();
				$amount = $subscription->payment * 100;
				
				$client_Intent = \Stripe\PaymentIntent::create([
					'amount' =>  $amount,
					'currency' => 'usd',
				]);
			}
			
			// recurring payment
			return view('front.customer.applesubscription')->with(['subscriptionPlans' => $subscriptionPlans, 'customer' => $customer,'subscription_plan'=>$subscription_plan,'intent'=>$intent,'url_error'=>$url_error,'client_Intent'=>json_encode($client_Intent)]);
			
		
	}
	//CODE FOR NEW CONTROLLER
	
	public function applesubscriptionIntent(Request $request){
		Stripe::setApiKey(env('STRIPE_SECRET'));
		header('Content-Type: application/json');
		
		$intent = \Stripe\PaymentIntent::create([
			'amount' => $request->amount,
			'currency' => 'usd',
		  ]);
		 
		$clientSecret = Arr::get($intent, 'client_secret');  
		
		return response()->json($clientSecret);    
	}
	
    public function applesubscriptionStore(Request $request)
    {
		$input = $request->all(); 
		//CREATE LOG 
		Storage::disk('public')->put('log/log.txt',print_r($input,TRUE));

		if(!empty(session()->get('key'))){
			
			$key = Session::get('key');
			$id = $key['user_id'];
			$email = $key['email'];
			$customer_type = $key['customer_type'];
		
			$customer = Customer::where(['id'=>$id,'email'=>$email,'customer_type'=>$customer_type])->first();
					
		}
		
		try {
		
			//CODE FOR ADD STRIPE RESPONSE IN TABLE
			StripeResponse::create(['customer_id'=>$customer->id,'email'=>$customer->email,'customer_type'=>$customer_type,'stripe_result'=>print_r($input,TRUE)]);

			//IF Customer  have already entry in subcription table with cancel status in then remove old row
			$subscript_exist = Subscription::where(['customer_id'=>$customer->id,'stripe_status'=>'canceled'])->first();
			if($subscript_exist){
				Subscription::where(['customer_id'=>$customer->id,'stripe_status'=>'canceled'])->delete();
			}
			

			$newSubscription = $customer->newSubscription('main', $request->subscription_plan)->create($request->payment_method, [
			'name' => $customer->name,
			'email' => $customer->email,
			'address' => [
				'line1' => ''.$input['address'].'',
				'postal_code' => ''.$input['zipcode'].'',
				'city' => ''.$input['city'].'',
				'state' => ''.$input['state'].'',
				'country' => 'USA',
				],
				
			]);	
			
		} catch ( IncompletePayment $exception ){
			DB::rollback();
			//return redirect()->back()->with(['error_message' => $exception->getMessage()]);
			//return redirect()->route('cashier.payment',[$exception->payment->id, 'redirect' => route('home')]);
			return redirect()->back()->withInput($request->input())->with('error', $exception->getMessage());
		
		}	
		
		$sub_id = $newSubscription->stripe_id;
		if($sub_id){
							
			$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
			$sub_response = $stripe->subscriptions->retrieve($sub_id,[]);
			


			//update more fields in subscriptions table 
			$start_date =  date('Y-m-d H:i:s', $sub_response->current_period_start);
			$exp_date = date('Y-m-d H:i:s', $sub_response->current_period_end);
			$plan_id = getsubscriptionId($sub_response->plan->product);// database plan id
		

			Subscription::where('customer_id', $customer->id)->update(["subscription_plan_id" => $plan_id, 'start_date' => $start_date, 'expiration_date' => $exp_date, 'status' => 1]);	


			$payment = PaymentHistory::create(
				[
					'customer_id' => $customer->id,
					'stripe_customer_id' => $customer->stripe_id,
					'stripe_subscription_id' => $sub_id,
					'stripe_plan_id' => $sub_response->plan->id,
					'subscription_plan_id' => $plan_id,
					'interval' => $sub_response->items->data[0]->price->recurring->interval,
					'price' => $sub_response->plan->amount/100,
					'status' => $sub_response->status,
					'invoice_id' => $sub_response->latest_invoice,
					'start_date' => date('Y-m-d H:i:s', $sub_response->current_period_start),
					'expiration_date' => date('Y-m-d H:i:s', $sub_response->current_period_end),
				]
			);	
			//DELETE PAYMENT TOKEN AFTER COMPLETE PAYMENT 
			PaymentToken::where(['customer_id'=>$customer->id,'customer_type'=>$customer->customer_type])->delete();
			
			//CODE FOR SEND EMAIL To CUSTOMER / VENDOR 
			$to = $customer->email;
			$subject = 'You Subscribed Successfully';
			$reply_message['name'] = $customer->name;
			$reply_message['price'] = $sub_response->plan->amount/100;
			$reply_message['interval'] = $sub_response->items->data[0]->price->recurring->interval;
			$reply_message['start_date'] = date('d F, Y', $sub_response->current_period_start);
			$reply_message['expiration_date'] = date('d F, Y', $sub_response->current_period_end);
			$reply_message['status'] = $sub_response->status;
			$frommail = env('ADMIN_EMAIL_ID', 'punchbugg21@gmail.com');
			$mail = Mail::to($to)->send(new SubscriptionMail($reply_message, $frommail, $subject));
			
			Session::flash('flash_success', 'You successfully purchase a subscription!'); 
			return response()->json('success');

		} else{
			//When Error comes
			$error_message = $exception->getMessage();
			if($error_message ==''){
				$error_message = 'Somthing is wrong.Please try again.';
			} 
			return redirect()->back()->withInput($request->input())->with('error', $error_message);
			//return redirect()->back()->with(['error_message' => $error_message]);	
		}
		
		
	}
	

	// FOR NORMAL PAYMENT 
	public function onetimeSubscriptionStore(Request $request)
    {
		$input = $request->all(); 
		
		
		if(!empty(session()->get('key'))){
			
			$key = Session::get('key');
			$id = $key['id'];
			$email = $key['email'];
			$customer_type = $key['customer_type'];
		
			$customer = Customer::where(['id'=>$id,'email'=>$email,'customer_type'=>$customer_type])->first();
				
		}
		//$customer = Auth::guard('customer')->user();
		
		//$customer = Customer::findOrFail(2);
		//Fetch subscription plan details
		$subsPlan = SubscriptionPlan::findOrFail($input["subscription_plan"]);
		
		
		//Charge Customer
		try {
			Stripe::setApiKey(env('STRIPE_SECRET'));

			$CustomerStripe = CustomerStripe::create(array(
				'email' => $request->stripeEmail,
				'source'  => $request->stripeToken
			));

			$charge = Charge::create(array(
				'customer' => $CustomerStripe->id,
				'amount'   => $subsPlan->payment*100, //convert into cents
				'currency' => 'usd'
			));
			
			$payment_id = $charge->id;
		} catch (\Exception $ex) {
			return redirect()->back()->withInput($request->input())->with('error', $ex->getMessage());
		}
		
		//Calculate expiration date
		$cur_date = date('Y-m-d');
		$date = date_create($cur_date); 		  
		date_add($date, date_interval_create_from_date_string("$subsPlan->expiration_number days")); 
		$exp_date = date_format($date, "Y-m-d"); 
		//Update subscription plan
		$subscription = Subscription::where('customer_id', $customer->id)->get()->first();
		if(!$subscription){
			// NEW RECORDS
			$subscription = new Subscription;
           	$subscription->customer_id = $customer->id;
			$subscription->subscription_plan_id = $input["subscription_plan"];
			$subscription->start_date = date('Y-m-d');
			$subscription->expiration_date = $exp_date;
			$subscription->status = 1;
			$subscription->payment_id = $payment_id;
			$subscription->save();
		}else {
			// UPDATE RECORDS
			Subscription::where('customer_id', $customer->id)->update(["subscription_plan_id" => $input["subscription_plan"], 'start_date' => date('Y-m-d'), 'expiration_date' => $exp_date, 'status' => 1, 'payment_id' => $payment_id]);
		}
		//Add Subscription details into payment history table
		$paymentHistory = new PaymentHistory;
		$paymentHistory->customer_id = $customer->id;
		$paymentHistory->subscription_plan_id = $input["subscription_plan"];
		$paymentHistory->start_date = date('Y-m-d');
		$paymentHistory->expiration_date = $exp_date;
		$paymentHistory->payment_id = $payment_id;
		$paymentHistory->save();
		
		//Delete Payment Token
		PaymentToken::where(['customer_id'=>$customer->id,'customer_type'=>$customer->customer_type])->delete();
		//return redirect()->back()->with('message', 'Subscription Plan has been renewed successfully');
		return redirect()->route('customer.paymentDone')->with('flash_success' , 'You successfully purchase a Subscription.');
	}

}