<x-mail::message>
# Thank You for Your Quote Request

Dear {{ $quote->name }},

We've received your quote request and appreciate your interest in RAW Disposal's waste management services. Our team is reviewing your requirements and will contact you within 24 hours with a customized solution.

## Your Quote Reference
**Quote Number:** {{ $quote->quote_number }}  
*Please reference this number in any communication about your quote.*

## What We Received

### Project Information
- **Project Type:** {{ $projectType }}
- **Location:** {{ $quote->location }}
- **Start Date:** {{ $startDate }}
@if($quote->duration)
- **Duration:** {{ $quote->duration }}
@endif

### Services Requested
@if(count($services) > 0)
@foreach($services as $service)
- {{ $service }}
@endforeach
@else
To be determined based on your project needs.
@endif

## What Happens Next?

1. **Review:** Our team is reviewing your specific requirements
2. **Customization:** We'll prepare a solution tailored to your needs
3. **Contact:** Expect to hear from us within 24 hours
4. **Quote:** You'll receive a detailed quote with transparent pricing

## Need Immediate Assistance?

If you have urgent requirements or questions, don't hesitate to reach out:

<x-mail::button :url="'tel:5041234567'">
Call Us: (504) 123-4567
</x-mail::button>

## Why Choose RAW Disposal?

- **Reliable Service:** 24/7 emergency response available
- **Local Expertise:** Serving Louisiana communities since [Year]
- **Complete Solutions:** From portable toilets to roll-off dumpsters
- **Environmental Commitment:** Responsible waste management practices

We look forward to serving you and making your project a success!

Best regards,  
**The RAW Disposal Team**

---

*This email confirms receipt of your quote request submitted on {{ $quote->created_at->format('F j, Y \a\t g:i A') }}.*

<x-mail::subcopy>
RAW Disposal | Your Trusted Waste Management Partner  
Serving Louisiana with Pride | License #12345  
www.rawdisposal.com | (504) 123-4567
</x-mail::subcopy>
</x-mail::message>
