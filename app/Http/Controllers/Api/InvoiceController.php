<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function get_invoices(Request $request)
    {
        $token= $request->bearerToken();
        $user = JWTAuth::authenticate($token);
        // $reference = $request->only('reference');
        if(isset($_GET['reference']) && !empty($_GET['reference'])){
            $reference = $_GET['reference'];
            $this->verify_payment_rave($reference);
        }

        // if(isset($_GET['client_id']) && !empty($_GET['client_id'])){
        //     $Id = $_GET['client_id'];
        //     $where = 'service_application.client_id';
        // } 

        // if(isset($_GET['id']) && !empty($_GET['id'])){
        //     $Id = $_GET['id'];
        //     $where = 'id';
        // }
       
        // if(!$Id) {
        //     return response()->json([
        //         'success' => true, 
        //         'message' => []]);  
        // }

        $invoices = DB::table('invoices')
        ->join('service_application', 'invoices.service_application_id', '=', 'service_application.id')
        ->where('service_application.applicant_id', $user->id)
        ->orWhere('service_application.client_id', $user->id)
        ->select('invoices.*', 'service_application.client_id', 'service_application.applicant_id')->orderBy('created_at', 'desc')->get();
        if(!$invoices) {
            return response()->json([
                'success' => true, 
                'message' => []]);            
        } else {
            foreach($invoices as $row) {
                $user = DB::table('users')->where('id', $row->client_id)->first();
                if($row->client_id != $row->applicant_id){
                    $applicant = DB::table('users')->where('id', $row->applicant_id)->first();
                    $row->applicant = $applicant;
                }
                $client = DB::table('users')->where('id', $row->client_id)->first();
                $row->client = $client;
        
            }
            
            return response()->json(['success' => true, 'message' => $invoices]);
        }
    }
    public function init_transaction(Request $request)
    {
        $data = $request->only('invoice_id', 'email', 'is_rave');
        $validator = Validator::make($data, [
            'invoice_id' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Required field cannot be blank.'
            ], 404);
        }

        $invoice = DB::table('invoices')->where('id', $request->invoice_id)->first();
        
        if(!$invoice) {
            return response()->json([
                'success' => false, 
                'message' => 'Record not found!']);          
        } else {
            $token= $request->bearerToken();
            $user = JWTAuth::authenticate($token);
            
            if($request->is_rave)
            return $this->initialize_rave($invoice, $user, $request->email);

            return $this->initialize($invoice, $user, $request->email);
        }
    }
    public function charge_callback(Request $request)
    {
        // Retrieve the request's body
        $event = $request->input('event');
        $email = $request->input('data.customer.email');
        $gateway_response = $request->input('data.gateway_response');
        $reference = $request->input('data.reference');

        Log::info($event);

        if($event === 'charge.success'){
            $dt = date('Y-m-d H:i:s');
            $transaction = DB::table('transactions')->where('reference', $reference)->first();
            Log::info('$transaction->invoice_id: '.$transaction->invoice_id);
            if($transaction){
                // Log::info('$user: '.$user->id);
                DB::table('invoices')->where('id', $transaction->invoice_id)->update(
                    ['is_paid' => 1,
                    'paid_by_user_id' => $transaction->customer_user_id,
                    'updated_at' => $dt,
                ]);


            DB::table('transactions')->where('id', $transaction->id)->update(
                ['gateway_response' => $transaction->gateway_response,
                'status' => $transaction->status,
                'transaction_date' => $transaction->transaction_date,
                'charge_attempted' => 1,
                'updated_at' => $dt,
            ]);
            // Log::info('$invoice: updated');
            }
        }
        return response()->json('processed', 200);
    }
    public function charge_callback_rave(Request $request)
    {
        Log::info('$hello 1234:');
        // Retrieve the request's body
        $event = $request->input('event');
        $status = $request->input('data.status');
        // $createdAt = $request->input('data.createdAt');
        $reference = $request->input('data.reference');

        Log::info('$reference: '.$reference);
        Log::info('$createdAt: '.$reference);
        Log::info('$status: '.$status);

       if($event === 'charge.success' && $status == 'SUCCESSFUL'){
        $this->verify_payment_rave($reference);
       // $this->update_transaction_as_paid($reference, $status, $createdAt);
            // Log::info('$invoice: updated');
       }
        return response()->json('processed', 200);
    }

    protected function update_transaction_as_paid($reference, $status, $createdAt){
        $dt = date('Y-m-d H:i:s');
            $transaction = DB::table('transactions')->where('reference', $reference)->first();
            //Log::info('$transaction->invoice_id: '.$transaction->invoice_id);
            if($transaction){
                // Log::info('$user: '.$user->id);
                DB::table('invoices')->where('id', $transaction->invoice_id)->update(
                    ['is_paid' => 1,
                    'paid_by_user_id' => $transaction->customer_user_id,
                    'updated_at' => $dt,
                ]);
                DB::table('transactions')->where('id', $transaction->id)->update(
                    ['gateway_response' => $status,
                    'status' => $status,
                    'transaction_date' => date("Y-m-d H:i:s", strtotime($createdAt)),
                    'charge_attempted' => 1,
                    'updated_at' => $dt,
                ]);
            }
    }
    protected function initialize($invoice, $user, $email)
    {

        $amount_in_kobo = $invoice->payment_amount  * 100;
        $currency = $invoice->currency;
        
        if(!$email)
            $email = $user->email;
        if(!$currency)
            $currency = "NGN";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount_in_kobo,
                'email' => $email,
                // 'phone' => 234868965986,
                'firstname' => $user->first_name,
                'lastname' => $user->last_name,



            ]),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer sk_test_c9b326fc0f60f878d988bf648234fd6b2a7b05cd", //replace this with your own test key
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            // there was an error contacting the Paystack API
            return response()->json([
                'success' => false, 
                'message' => $err]);   
           // die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);
     

        if ($tranx['status'] != true) {
            // there was an error from the API
            return response()->json([
                'success' => false, 
                'message' => $tranx['message']]);   
            // print_r('API returned error: ' . $tranx['message']);
        }
  

        $data = $tranx['data'];
        $data['invoice_id'] = $invoice->id;
        $data['customer_user_id'] = $user->id;
        $data['customer_email'] = $email;
        $data['amount'] = $invoice->payment_amount;
        $data['payment_name'] = $invoice->payment_name;
        $data['currency'] = $currency;
        DB::table('transactions')->insert($data);
        return response()->json([
            'success' => true, 
            'message' => $data]);   
    }

    protected function verify_payment_rave($reference){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/v2/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'txref'=> $reference,
                'SECKEY' => 'FLWSECK_TEST-1bf52708f0d3dd1274a2be427db26a86-X',
            ]),
            CURLOPT_HTTPHEADER => [
                // "authorization: Bearer sk_test_c9b326fc0f60f878d988bf648234fd6b2a7b05cd", //replace this with your own test key
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            // there was an error contacting the Paystack API
            return response()->json([
                'success' => false, 
                'message' => $err]);   
           // die('Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);
     

        if ($tranx['status'] != "success") {
            // there was an error from the API
            return response()->json([
                'success' => false, 
                'message' => $tranx['message']]);   
            // print_r('API returned error: ' . $tranx['message']);
        }else{
            $this->update_transaction_as_paid($reference, $tranx['status'], $tranx['data']['created']);
         return response()->json([
             'success' => true, 
             'message' => 'success']);
        }
    }
    protected function initialize_rave($invoice, $user, $email)
    {

        $amount_in_kobo = $invoice->payment_amount  * 100;
        $txref = "rave" . uniqid();
        $currency = $invoice->currency;
        
        if(!$email)
            $email = $user->email;
            if(!$currency)
                $currency = "NGN";

        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/hosted/pay",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => json_encode([
        //         'amount' => $invoice->payment_amount, //$amount_in_kobo,
        //         'email' => 'ogbonnagideon5@gmail.com',
        //         'currency' => $currency,
        //         'txref'=> $txref,
        //         'custom_title' => $invoice->payment_name,
        //         'PBFPubKey' => 'FLWPUBK_TEST-c20714b252f9a3ec74d95865df564375-X',
        //         'firstname' => $user->first_name,
        //         'lastname' => $user->last_name,
        //     ]),
        //     CURLOPT_HTTPHEADER => [
        //         // "authorization: Bearer sk_test_c9b326fc0f60f878d988bf648234fd6b2a7b05cd", //replace this with your own test key
        //         "content-type: application/json",
        //         "cache-control: no-cache"
        //     ],
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // if ($err) {
        //     // there was an error contacting the Paystack API
        //     return response()->json([
        //         'success' => false, 
        //         'message' => $err]);   
        //    // die('Curl returned error: ' . $err);
        // }

        // $tranx = json_decode($response, true);
     

        // if ($tranx['status'] != "success") {
        //     // there was an error from the API
        //     return response()->json([
        //         'success' => false, 
        //         'message' => $tranx['message']]);   
        //     // print_r('API returned error: ' . $tranx['message']);
        // }
  

        // $data = $tranx['data'];
        $data = json_decode("{}", true);
        $data['reference'] = $txref;
        $data['is_flutterwave'] = true;
        // $data['link'] = true;
        $data['invoice_id'] = $invoice->id;
        $data['customer_user_id'] = $user->id;
        $data['customer_email'] = $email;
        $data['amount'] = $invoice->payment_amount;
        $data['payment_name'] = $invoice->payment_name;
        $data['currency'] = $currency;
        DB::table('transactions')->insert($data);
        return response()->json([
            'success' => true, 
            'message' => $data]);   
    }
}