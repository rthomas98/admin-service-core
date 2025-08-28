<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Customer;
use App\Mail\QuoteSubmitted;
use App\Mail\QuoteConfirmation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    /**
     * Store a newly created quote from the public form.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'projectType' => 'required|string|max:255',
            'services' => 'nullable|array',
            'services.*' => 'string|max:255',
            'startDate' => 'required|date|after_or_equal:today',
            'duration' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Try to find existing customer or create a new one
            $customer = Customer::where('emails', $request->email)
                ->where('company_id', 1) // Raw Disposal
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'company_id' => 1, // Raw Disposal
                    'name' => $request->name,
                    'emails' => $request->email,
                    'phone' => $request->phone,
                    'company_name' => $request->company,
                    'address' => $request->location,
                ]);
            }

            // Parse location to extract city and state if possible
            $locationParts = explode(',', $request->location);
            $city = isset($locationParts[0]) ? trim($locationParts[0]) : $request->location;
            $state = isset($locationParts[1]) ? trim($locationParts[1]) : 'LA';

            // Create the quote
            $quote = Quote::create([
                // Form fields
                'name' => $request->name,
                'company' => $request->company,
                'email' => $request->email,
                'phone' => $request->phone,
                'project_type' => $request->projectType,
                'services' => $request->services,
                'start_date' => $request->startDate,
                'duration' => $request->duration,
                'location' => $request->location,
                'message' => $request->message,
                
                // Quote management fields
                'company_id' => 1, // Raw Disposal
                'customer_id' => $customer->id,
                'quote_number' => Quote::generateQuoteNumber(),
                'quote_date' => now(),
                'valid_until' => now()->addDays(30),
                'status' => 'new',
                
                // Delivery fields based on location
                'delivery_address' => $request->location,
                'delivery_city' => $city,
                'delivery_parish' => $state,
                'requested_delivery_date' => $request->startDate,
            ]);

            DB::commit();

            // Send email notifications
            try {
                // Send notification to admin/sales team
                $adminEmail = config('mail.admin_email', 'sales@rawdisposal.com');
                Mail::to($adminEmail)->send(new QuoteSubmitted($quote));
                
                // Send confirmation to customer
                Mail::to($quote->email)->send(new QuoteConfirmation($quote));
                
                Log::info('Quote emails sent successfully', [
                    'quote_id' => $quote->id,
                    'customer_email' => $quote->email,
                ]);
            } catch (\Exception $e) {
                // Log email error but don't fail the request
                Log::error('Failed to send quote emails', [
                    'quote_id' => $quote->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Log the successful quote submission
            Log::info('Quote submitted successfully', [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'customer_email' => $quote->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your quote request! We\'ll contact you within 24 hours.',
                'quote_number' => $quote->quote_number,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Quote submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Store a quote from LIV Transport website.
     */
    public function storeLivTransport(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'projectType' => 'required|string|max:255',
            'services' => 'nullable|array',
            'services.*' => 'string|max:255',
            'startDate' => 'required|date|after_or_equal:today',
            'duration' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'message' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Try to find existing customer or create a new one
            $customer = Customer::where('emails', $request->email)
                ->where('company_id', 3) // LIV Transport
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'company_id' => 3, // LIV Transport
                    'name' => $request->name,
                    'emails' => $request->email,
                    'phone' => $request->phone,
                    'company_name' => $request->company,
                    'address' => $request->location,
                ]);
            }

            // Parse location to extract city and state if possible
            $locationParts = explode(',', $request->location);
            $city = isset($locationParts[0]) ? trim($locationParts[0]) : $request->location;
            $state = isset($locationParts[1]) ? trim($locationParts[1]) : 'LA';

            // Generate quote number with LIV prefix
            $quoteNumber = 'LIV' . now()->format('ymd') . str_pad(
                Quote::where('company_id', 3)
                    ->whereDate('created_at', now())
                    ->count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

            // Create the quote
            $quote = Quote::create([
                // Form fields
                'name' => $request->name,
                'company' => $request->company,
                'email' => $request->email,
                'phone' => $request->phone,
                'project_type' => $request->projectType,
                'services' => $request->services,
                'start_date' => $request->startDate,
                'duration' => $request->duration,
                'location' => $request->location,
                'message' => $request->message,
                
                // Quote management fields
                'company_id' => 3, // LIV Transport
                'customer_id' => $customer->id,
                'quote_number' => $quoteNumber,
                'quote_date' => now(),
                'valid_until' => now()->addDays(30),
                'status' => 'new',
                
                // Delivery fields based on location
                'delivery_address' => $request->location,
                'delivery_city' => $city,
                'delivery_parish' => $state,
                'requested_delivery_date' => $request->startDate,
            ]);

            DB::commit();

            // Send email notifications
            try {
                // Send notification to LIV Transport admin/sales team
                $adminEmail = 'livtransportllc@gmail.com';
                Mail::to($adminEmail)->send(new \App\Mail\LivTransportQuoteSubmitted($quote));
                
                // Send confirmation to customer
                Mail::to($quote->email)->send(new \App\Mail\LivTransportQuoteConfirmation($quote));
                
                Log::info('LIV Transport quote emails sent successfully', [
                    'quote_id' => $quote->id,
                    'customer_email' => $quote->email,
                ]);
            } catch (\Exception $e) {
                // Log email error but don't fail the request
                Log::error('Failed to send LIV Transport quote emails', [
                    'quote_id' => $quote->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Log the successful quote submission
            Log::info('LIV Transport quote submitted successfully', [
                'quote_id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'customer_email' => $quote->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your quote request! We\'ll contact you within 24 hours.',
                'quote_number' => $quote->quote_number,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('LIV Transport quote submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again.',
            ], 500);
        }
    }
}
