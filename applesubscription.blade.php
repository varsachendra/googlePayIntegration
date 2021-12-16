@extends('layouts.frontApp')
@section('content')
<style>
    .StripeElement {
        background-color: white;
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid transparent;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }
    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }
    .StripeElement--invalid {
        border-color: #fa755a;
    }
    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
	#feature {
		padding-top:0;
	}
	.resetbox {
		padding-top:0;
	}
	.login-box-msg, .register-box-msg {
		padding-bottom:0;
	}
	.form-group, .form-control {
		margin-bottom:0;
	}
	#renew_subscription_form .col-sm-6 {
		 width: 50%;
		float: left;
	}
    #apple-pay-button {
        width: 280px;
        height: 64px;
        display: inline-block;
        box-sizing: border-box;
        background-image: url("{{ URL::asset('dist/img/ApplePayBTN_32pt__black_textLogo_@2x.png') }}");
        background-size: 100%;
        background-repeat: no-repeat;
        margin-top: 10px;
        border: 0;
    }
   
</style>

<div class="header-section text-center">
    <h2 class="title" style="font-size: 30px;">{{__('Subscription')}}</h2>      
</div>
<section id="feature">
    <div class="container">
        <div class="row">
            <div class="aboutus">
                <div class="resetbox text-center">
                    @include('admin.includes.showError')
                    @foreach (['danger', 'warning', 'success', 'info'] as $key)
                        @if(Session::has($key))
                            <p class="alert alert-{{ $key }}">{{ Session::get($key) }}</p>
                        @endif
                    @endforeach

                    <p class="login-box-msg" id="jquery_error"></p>
                    <form id="renew_subscription_form" method="POST" action="{{ route('customer.subscriptionStore') }}" class="form-horizontal" data-stripe-publishable-key="{{ env('STRIPE_KEY') }}">
                    @csrf
                    <div id="customer_details">
                        <div class="form-group">
                        <label for="inputEmail3" class="control-label text-left">{{ __('Name') }}</label>
                            <div class="col-sm-12">
                            <input id="provider-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="Sachendra Kumar" required autocomplete="name" autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>    
                        </div>
                        <div class="form-group">
                        <label for="inputEmail3" class="control-label text-left">{{ __('Address') }}</label>
                            <div class="col-sm-12">
                            <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="4105 Avenel Boulevard" required autocomplete="address" autofocus>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>    
                        </div>
                        <div class="form-group">
                        <label for="inputEmail3" class="control-label text-left">{{ __('City') }}</label>
                            <div class="col-sm-12">
                            <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="North Wales" required autocomplete="city" autofocus>
                            @error('city')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>    
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                <label for="inputEmail3" class="control-label text-left">{{ __('State') }}</label>
                                    <div class="col-sm-12">
                                    <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="PA" required autocomplete="state" autofocus>
                                    @error('state')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>    
                                </div>   
                            </div>   
                            <div class="col-sm-6">
                                <div class="form-group">
                                <label for="inputEmail3" class="control-label text-left">{{ __('Zipcode') }}</label>
                                    <div class="col-sm-12">
                                    <input id="zipcode" type="text" class="form-control @error('zipcode') is-invalid @enderror" name="zipcode" value="19454" required autocomplete="zipcode" autofocus  minlength="5" maxlength="5" onkeypress="return isNumberKey(event);"  inputmode="numeric">
                                    @error('zipcode')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>    
                                </div>
                            </div>
                        </div>

                            <div class="form-group">
                                <label for="inputEmail3" class="control-label text-left">{{ __('Subscription Plan') }}</label>

                                <div class="col-sm-12">
                                    <select name="subscription_plan" id="subscription_plan" class="form-control">
                                        @foreach($subscriptionPlans as $subscriptionPlan)
                                            @php $subsPlan = $subscriptionPlan->payment; @endphp
                                            <option value="{{$subscriptionPlan->price_id}}" @if($subscription_plan ==$subscriptionPlan->id) selected @endif>{{$subscriptionPlan->title}} ( ${{$subscriptionPlan->payment}} / {{$subscriptionPlan->expiration_number}} @if($subscriptionPlan->expiration_number>1) {{$subscriptionPlan->expiration_period}}s @else {{$subscriptionPlan->expiration_period}} @endif )</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                        </div> 
                        <div class="form-group text-center" style="margin-top:10px;">
                            <button id="prevoius" class="btn-success pull-left"  onclick="showBackStep();" style="display:none;margin-left:15px;">PRE. STEP</button>
                            <button id="next" class="btn-success pull-right" style="margin-right:15px;" onclick="showNextStep();">NEXT STEP</button>
                        </div>   
						<br>
                        <div id="payment_div" style="display:none;">
                        <div id="pay_method" class="form-group" style="text-align:left; margin-left: 0px;">
                        <label for="inputEmail3" class="control-label text-left" style="padding: 0px 15px 20px 0px;">{{ __('Payment Method') }}</label>  
                            <input type="radio" name="payment_mode" id="credit_card" value="creditcard" checked/>
                            <label for="creditcard" style="margin-right: 5px;">Credit or debit card</label>
                            <input type="radio" name="payment_mode" id="other_pay" value="googlepay" style="display:none;" />
                            <label id="other_paylbl" for="googlepay" style="display:none;">Google or Apple Pay</label>
                        </div>
                            <div id="credit_card_pay">
                                <div class="form-row">
                                    <!--<label for="card-element" style="margin-right: 224px;">Credit or debit card</label>-->
                                    <div id="card-element" class="form-control">
                                    </div>
                                    <!-- Used to display form errors. -->
                                    <div id="card-errors" role="alert"></div>
                                </div>
                                <div class="stripe-errors"></div>
                                @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                    @endforeach
                                </div>
                                @endif
                                <div class="form-group text-center" style="margin-top:10px;">
                                    <button  id="card-button" data-secret="{{ $intent->client_secret }}" class="btn btn-lg btn-success btn-block" @if($url_error == true) disabled  @endif>SUBMIT</button>
                                </div>
                            </div>
                            <div id="other_pay_div" style="display:none;">
                                <div id="payment-request-button"></div>
                                <button id="apple-pay-button" style="display:none"></button>
                            </div>  
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
    <div id="loader"></div>
    <div id="loader_ajax"></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<?php
  $mybrowser = getBrowser();
  if($mybrowser['name'] =="Safari"){//APPLE PAY BUTTON CONDITION
?>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<script>
  
//Stripe.setPublishableKey('{{ env("STRIPE_KEY") }}'); 
Stripe.setPublishableKey("pk_test_51IgRzMI29lnP5KBaitbje17j1vxuDU0fMf1uJebs4aCUC9fMU4z67NVIXuqGyBEoDoChnYLb7sbvkCtFyK74yUPM00yYgK3Km9");
Stripe.applePay.checkAvailability(function(available) {
        if (available) {
            document.getElementById('apple-pay-button').style.display = 'block';
            console.log('hi, I can do ApplePay');
            $('#other_pay').show();
            $('#other_paylbl').show()
        } else {
            $('#other_pay').hide();
            $('#other_paylbl').hide();
            console.log('ApplePay is possible on this browser, but not currently activated.');
        }
    });
document.getElementById('apple-pay-button').addEventListener('click', beginApplePay);
    var price ="{{$subscriptionPlan->payment}}";
    
    function beginApplePay() {
        var paymentRequest = {
            requiredBillingContactFields: ['postalAddress'],
            requiredShippingContactFields: ['phone'],
            countryCode: 'US',
            currencyCode: 'USD',
            total: {
                label: '{{$subscriptionPlan->title}} Subscription',
                amount: price
            }
        };
        var session = Stripe.applePay.buildSession(paymentRequest,
            function(result, completion) {
            
                //console.log(result.token.card.address_line1);
                var name = $('#provider-name').val();
                var address = $('#address').val();
                var city = $('#city').val();
                var state = $('#state').val();
                var zipcode = $('#zipcode').val();
                var plan = $('#subscription_plan').val();
                $.post('{{ route("customer.applesubscriptionStore") }}', { result: result, token: result.token.id, 
                price: "{{$subscriptionPlan->payment}}", 
                id: "{{$subscriptionPlan->id}}",
                    name: name,
                    address:address,
                    city:city,
                    state:state,
                    zipcode:zipcode,
                    subscription_plan:plan   
                }).done(function() {
                completion(ApplePaySession.STATUS_SUCCESS);
                // Prevent the form from submitting with the default action
                //return false;
                // You can now redirect the user to a receipt page, etc.
                window.location.href = "{{route('customer.applepaymentDone')}}"; 
                }).fail(function() {
                    completion(ApplePaySession.STATUS_FAILURE);
                });
            }, function(error) {
                console.log(error.message);
            });
        session.begin();
    }
</script>
<?php 
  }
?>
    <!-- JQUERY File -->

<script src="https://js.stripe.com/v3/"></script>


<?php 
//preg_match("/Android|webOS/", $_SERVER['HTTP_USER_AGENT'], $matches) &&
if($mybrowser['name'] =="Chrome"){  
    $client_Intent = json_decode($client_Intent);
   //echo env("STRIPE_SECRET");
    //exit;
?>    
<script>
//CODE FOR GOOGLE PAY COE 
document.addEventListener('DOMContentLoaded', async () => {
    //const stripe = Stripe('pk_test_51IgRzMI29lnP5KBaitbje17j1vxuDU0fMf1uJebs4aCUC9fMU4z67NVIXuqGyBEoDoChnYLb7sbvkCtFyK74yUPM00yYgK3Km9');
    const stripe = Stripe('{{ env("STRIPE_KEY") }}');
    
    const paymentRequest = stripe.paymentRequest({
        currency:'usd',
        country:'US',
        requestPayerName:true,
        requestPayerEmail:true,
        total:{
            label:'<?php echo $subscriptionPlan->title." Subscription";?>',
            amount: <?php echo $subscriptionPlan->payment*100?>,
        }
    });
    const element= stripe.elements();
    const prButton = element.create('paymentRequestButton',{
        paymentRequest:paymentRequest,
    });
    paymentRequest.canMakePayment().then((result) => {
        //alert(result+"sssss");
        if(result){
            //alert("bye");
                //mount the element
                prButton.mount('#payment-request-button');
                //$('#google_pay').hide();
                //$('#googlepaylbl').hide();
                $('#other_pay').show();
                $('#other_paylbl').show()
            }else {
               // alert("hey");
                $('#other_pay').hide();
                $('#other_paylbl').hide();
                document.getElementById('payment-request-button').style.display='none';
                addMessage('GooglePay is unavaible');
                //console.log0('GooglePay is unavaible');
            }
    });
    /*
    // CREATE PAYMENT INTENT 
    $('input[name="payment_mode"]').on('change', function() {
        var radioValue = $('input[name="payment_mode"]:checked').val();    
        if(radioValue =="googlepay"){
            $.ajax({
                type:'POST',
                url:"{{ route('customer.applesubscriptionIntent') }}",
                data:{amount:'<?php //echo $subscriptionPlan->payment*100?>'},
                success:function(data){
                    const clientSecret  = data;
                    alert(clientSecret);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Some error occur in Google Pay");
                    console.log(jqXHR.responseJSON.errors);
                }
            });
        }
   });
   */

  paymentRequest.on('paymentmethod', function(ev) {
    const clientSecret = '<?php echo  $client_Intent->client_secret;?>';
  // Confirm the PaymentIntent without handling potential next actions (yet).
  stripe.confirmCardPayment(
    clientSecret,
    {payment_method: ev.paymentMethod.id},
    {handleActions: false}
  ).then(function(confirmResult) {
        console.log(confirmResult);
    if (confirmResult.error) {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete('fail');
    } else {
        
       // alert(confirmResult.paymentIntent.status);
      // Report to the browser that the confirmation was successful, prompting
      // it to close the browser payment method collection interface.
      ev.complete('success');
      // Check if the PaymentIntent requires any actions and if so let Stripe.js
      // handle the flow. If using an API version older than "2019-02-11"
      // instead check for: `paymentIntent.status === "requires_source_action"`.
      if (confirmResult.paymentIntent.status === "requires_action") {
          console.log("if");
        // Let Stripe.js handle the rest of the payment flow.
        stripe.confirmCardPayment(clientSecret).then(function(result) {
          if (result.error) {
            // The payment failed -- ask your customer for a new payment method.
          } else {
            // The payment has succeeded.
            var name = $('#provider-name').val();
            var address = $('#address').val();
            var city = $('#city').val();
            var state = $('#state').val();
            var zipcode = $('#zipcode').val();
            var plan = $('#subscription_plan').val();
            $.post('{{ route("customer.applesubscriptionStore") }}', { 
                    result:result,
                    //payment_method: ev.paymentMethod.id,
                    //token: paymentIntent.id, 
                    name: name,
                    address:address,
                    city:city,
                    state:state,
                    zipcode:zipcode,
                    subscription_plan:plan  
                }).done(function() {
                    window.location.href =  "{{route('customer.applepaymentDone')}}"; 
                }).fail(function() {
                    console.log("error");
                });
          }
        });
      } else {
            console.log("else");
        // The payment has succeeded.
            var name = $('#provider-name').val();
            var address = $('#address').val();
            var city = $('#city').val();
            var state = $('#state').val();
            var zipcode = $('#zipcode').val();
            var plan = $('#subscription_plan').val();
            $.post('{{ route("customer.applesubscriptionStore") }}', { 
                result: confirmResult,
                payment_method: confirmResult.paymentIntent.payment_method,
                token: confirmResult.paymentIntent.id, 
                name: name,
                address:address,
                city:city,
                state:state,
                zipcode:zipcode,
                subscription_plan:plan  
            }).done(function() {
                window.location.href =  "{{route('customer.applepaymentDone')}}"; 
            }).fail(function() {
                console.log("error");
            });
        }
    }
  });
});


    /*
    paymentRequest.on('paymentmethod',async(e) => {
        const clientSecret = '<?php //echo  $client_Intent->client_secret;?>';
        
            const{error,paymentIntent} = await stripe.confirmCardPayment(clientSecret,{
                    payment_method:e.paymentMethod.id
            },{handleActions:false});
            if(error){
                alert("error");
                e.complete('fail');
            } else {//Success
                e.complete('success');
                //stripe.confirmCardPayment(clientSecret);
                
                var name = $('#provider-name').val();
                var address = $('#address').val();
                var city = $('#city').val();
                var state = $('#state').val();
                var zipcode = $('#zipcode').val();
                var plan = $('#subscription_plan').val();
                $.post('{{ route("customer.applesubscriptionStore") }}', { 
                    result:paymentIntent,
                    payment_method: e.paymentMethod.id,
                    token: paymentIntent.id, 
                    name: name,
                    address:address,
                    city:city,
                    state:state,
                    zipcode:zipcode,
                    subscription_plan:plan  
                }).done(function() {
                    window.location.href =  "{{route('customer.applepaymentDone')}}"; 
                }).fail(function() {
                    console.log("error");
                });
            }
            console.log(e);
    });
    */

});

</script>
<?php 
}
?>
<script>
	var spinner = $('#loader');
		
        const stripe = Stripe('{{ env("STRIPE_KEY") }}');
        console.log(stripe);
        const elements = stripe.elements();
        //const cardElement = elements.create('card');
        const cardElement = elements.create('card',{
            hidePostalCode : true
        });
        cardElement.mount('#card-element');
        const cardHolderName = document.getElementById('provider-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;
        let validCard = false;
        const cardError = document.getElementById('card-errors');
        cardElement.addEventListener('change', function(event) {
            
            if (event.error) {
                validCard = false;
                cardError.textContent = event.error.message;
            } else {
                validCard = true;
                cardError.textContent = '';
            }
        });
        
        var form = document.getElementById('renew_subscription_form');
        form.addEventListener('submit', async (e) => {
            spinner.show();
            event.preventDefault();
            const { paymentMethod, error } = await stripe.createPaymentMethod(
                'card', cardElement, {
                    billing_details: { name: cardHolderName.value }
                }
            );
            if (error) {
                // Display "error.message" to the user...
                spinner.hide();
                console.log(error);
                // SHowing Error below credit card details
                $('#card-element').removeClass('StripeElement--complete');
                $('#card-element').addClass('StripeElement--invalid');
                cardError.textContent = error.message;
            } else {
                // The card has been verified successfully...
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payment_method');
                hiddenInput.setAttribute('value', paymentMethod.id);
                form.appendChild(hiddenInput);
                // Submit the form
                //form.submit();
                
                
                //validate fields
                var fail = false;
                var fail_log = '';
                var name;
                $('form#renew_subscription_form').find('select, textarea, input').each(function(){
                    if(!$(this).prop('required')){
    
                    }else{
                        if(!$(this).val()){
                            fail = true;
                            fd_id = $(this).attr('id');
    
                            var $window = $(window),
                            $element = $('#'+fd_id),
                            elementTop = $element.offset().top,
                            elementHeight = $element.height(),
                            viewportHeight = $window.height(),
                            scrollIt = elementTop - ((viewportHeight - elementHeight) / 2);
                            $window.scrollTop(scrollIt);
                                
                            $('#'+fd_id).focus();	
                            fail_log = fd_id + " is required \n";
                        }
                    }
                });
                
                if(fail_log.length == 0){	
                    //$('#renew_subscription_form').submit();		
                            
                    var data=$('#renew_subscription_form')[0];  
                    var fd = new FormData(data);
                    $.ajax({
                        type:'POST',
                        url:"{{ route('customer.applesubscriptionStore') }}",
                        data:fd,
                        processData: false,  // Important!
                        contentType: false,
                        cache: false,
                        beforeSend:function(){
                            $('#loader_ajax').show();
                        },
                        success:function(data){
                            $('#loader_ajax').hide();
                            //console.log();
                            // return false;
                            window.location.href= "{{route('customer.applepaymentDone')}}";   
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            //console.log(jqXHR.responseJSON.errors)
                            $('.alert-danger').show();
                            $('.alert-success').hide();
                            $('#loader_ajax').hide();
                            $.each(jqXHR.responseJSON.errors, function(key,value) {
                                $('html, body').animate({
                                    scrollTop: $(".alert-danger").offset().top
                                }, 2000);
                                $(".alert-danger").find("ul").append('<li>'+value+'</li>');
                            }); 

                        }
                    });	          
                } else{
                    //alert(fail_log);
                }
            }
        });

        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 8 && charCode != 9)
                return false;
            return true;
        }
        function showNextStep(){
            var fail = false;
            var fail_log = '';
            $('form#renew_subscription_form').find('select, textarea, input').each(function(){
                if(!$(this).prop('required')){
    
                }else{
                    if(!$(this).val()){
                        fail = true;
                        fd_id = $(this).attr('id');
                        var $window = $(window),
                        $element = $('#'+fd_id),
                        elementTop = $element.offset().top,
                        elementHeight = $element.height(),
                        viewportHeight = $window.height(),
                        scrollIt = elementTop - ((viewportHeight - elementHeight) / 2);
                        $window.scrollTop(scrollIt);    
                        $('#'+fd_id).focus();	
                        fail_log = fd_id + " is required \n";
                    }else if($(this).attr('id') =="zipcode" && $('#zipcode').val().length !=5){
                        fail = true;
                        fd_id = $(this).attr('id');
                        $('#'+fd_id).focus();	
                        fail_log = fd_id + " is 5 digit \n";
                    }
                }
            });
            if(fail_log.length == 0){	
                $("#customer_details").slideUp();
                $('#payment_div').show();
                $('#prevoius').show();
                $('#next').hide();
            }    
        }
        function showBackStep(){
            $("#customer_details").slideDown();
            $('#next').show();
            $('#prevoius').hide();
            $('#payment_div').hide();
        }
        $(function(){
            $('input[name="payment_mode"]').on('change', function() {
                var radioValue = $('input[name="payment_mode"]:checked').val();    
                if(radioValue =="creditcard"){
                    $('#other_pay_div').hide();
                    $('#credit_card_pay').show();
                }else {
                    $('#credit_card_pay').hide();
                    $('#other_pay_div').show(); 
                }
            });
        });
</script>
</section>
@endsection
