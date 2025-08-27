<x-mail::message>
# New Quote Request Received

A new quote request has been submitted through the RAW Disposal website.

## Contact Information
**Name:** {{ $quote->name }}  
**Company:** {{ $quote->company ?: 'Not provided' }}  
**Email:** {{ $quote->email }}  
**Phone:** {{ $quote->phone }}  

## Project Details
**Quote Number:** {{ $quote->quote_number }}  
**Project Type:** {{ $projectType }}  
**Location:** {{ $quote->location }}  
**Start Date:** {{ $startDate }}  
**Duration:** {{ $quote->duration ?: 'Not specified' }}  

## Services Requested
@if(count($services) > 0)
@foreach($services as $service)
- {{ $service }}
@endforeach
@else
No specific services selected.
@endif

## Additional Information
@if($quote->message)
{{ $quote->message }}
@else
*No additional information provided.*
@endif

<x-mail::button :url="config('app.url') . '/admin/1/quotes/' . $quote->id">
View Quote in Admin Panel
</x-mail::button>

---

**Important:** Please respond to this quote request within 24 hours as promised on our website.

This email was generated automatically. To reply to the customer, use their email address: {{ $quote->email }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
