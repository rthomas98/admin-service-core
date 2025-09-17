import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, Building2, Check } from 'lucide-react';
import { useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';

interface Props {
    serviceProvider: {
        id: number;
        name: string;
        slug: string;
        type: string;
    };
    user: {
        name: string;
        email: string;
    };
    customer?: {
        id: number;
        organization?: string;
        phone?: string;
        address?: string;
        city?: string;
        state?: string;
        zip?: string;
    };
}

interface SetupFormData {
    // Customer Information
    organization: string;
    business_type: string;
    tax_exemption_details: string;
    tax_exempt_reason: string;

    // Contact Information
    phone: string;
    phone_ext: string;
    secondary_phone: string;
    secondary_phone_ext: string;
    fax: string;
    fax_ext: string;

    // Address Information
    address: string;
    secondary_address: string;
    city: string;
    state: string;
    zip: string;
    county: string;

    // Other Details
    delivery_method: string;
    referral: string;
    internal_memo: string;
}

export default function Setup({ serviceProvider, user, customer }: Props) {
    const [currentStep, setCurrentStep] = useState(1);
    const totalSteps = 3;

    const { data, setData, post, processing, errors } = useForm<SetupFormData>({
        // Customer Information
        organization: customer?.organization || '',
        business_type: '',
        tax_exemption_details: '',
        tax_exempt_reason: '',

        // Contact Information
        phone: customer?.phone || '',
        phone_ext: '',
        secondary_phone: '',
        secondary_phone_ext: '',
        fax: '',
        fax_ext: '',

        // Address Information
        address: customer?.address || '',
        secondary_address: '',
        city: customer?.city || '',
        state: customer?.state || '',
        zip: customer?.zip || '',
        county: '',

        // Other Details
        delivery_method: '',
        referral: '',
        internal_memo: '',
    });

    const businessTypes = [
        'Corporation',
        'LLC',
        'Partnership',
        'Sole Proprietorship',
        'Non-Profit',
        'Government',
        'Construction',
        'Manufacturing',
        'Retail',
        'Healthcare',
        'Other',
    ];

    const deliveryMethods = [
        'Email',
        'Mail',
        'Both',
        'None',
    ];

    const states = [
        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
        'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
        'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
        'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
        'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
    ];

    const progress = (currentStep / totalSteps) * 100;

    const handleNext = () => {
        if (currentStep < totalSteps) {
            setCurrentStep(currentStep + 1);
        }
    };

    const handlePrevious = () => {
        if (currentStep > 1) {
            setCurrentStep(currentStep - 1);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/customer/setup');
    };

    const isLivTransport = serviceProvider.slug === 'liv-transport';
    const brandColor = isLivTransport ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700';
    const brandAccent = isLivTransport ? 'border-blue-600' : 'border-green-600';
    const serviceType = isLivTransport ? 'Transport Services' : 'Waste Management Services';

    return (
        <AuthCardLayout
            title="Complete Your Customer Profile"
            description="Set up your business profile to start using our services"
        >
            <Head title="Customer Setup - Service Core" />

            <div className="space-y-6">
                {/* Progress Indicator */}
                <div className="space-y-2">
                    <div className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">Step {currentStep} of {totalSteps}</span>
                        <span className="font-medium">{Math.round(progress)}% Complete</span>
                    </div>
                    <Progress value={progress} className="h-2" />
                </div>

                {/* Service Provider Badge */}
                <Card className={`border-2 ${brandAccent} bg-gradient-to-r ${
                    isLivTransport
                        ? 'from-blue-50 to-blue-100 dark:from-blue-950 dark:to-blue-900'
                        : 'from-green-50 to-green-100 dark:from-green-950 dark:to-green-900'
                }`}>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <Building2 className={`h-5 w-5 ${isLivTransport ? 'text-blue-600' : 'text-green-600'}`} />
                                <div>
                                    <p className="text-xs text-muted-foreground">Customer of {serviceProvider.name}</p>
                                    <p className="font-semibold text-foreground">{serviceType}</p>
                                </div>
                            </div>
                            <Badge variant="secondary" className="capitalize">
                                Customer Account
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Step 1: Customer Information */}
                    {currentStep === 1 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Business Information</CardTitle>
                                <CardDescription>
                                    Tell us about your business
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="organization">Business/Organization Name *</Label>
                                    <Input
                                        id="organization"
                                        type="text"
                                        value={data.organization}
                                        onChange={e => setData('organization', e.target.value)}
                                        required
                                        placeholder="Enter your business name"
                                        autoFocus
                                    />
                                    <InputError message={errors.organization} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="business_type">Business Type *</Label>
                                    <Select
                                        value={data.business_type}
                                        onValueChange={(value) => setData('business_type', value)}
                                    >
                                        <SelectTrigger id="business_type">
                                            <SelectValue placeholder="Select business type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {businessTypes.map((type) => (
                                                <SelectItem key={type} value={type}>
                                                    {type}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.business_type} />
                                </div>

                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="tax_exemption_details">Tax Exemption Number</Label>
                                        <Input
                                            id="tax_exemption_details"
                                            type="text"
                                            value={data.tax_exemption_details}
                                            onChange={e => setData('tax_exemption_details', e.target.value)}
                                            placeholder="If tax exempt"
                                        />
                                        <InputError message={errors.tax_exemption_details} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="tax_exempt_reason">Tax Exempt Reason</Label>
                                        <Input
                                            id="tax_exempt_reason"
                                            type="text"
                                            value={data.tax_exempt_reason}
                                            onChange={e => setData('tax_exempt_reason', e.target.value)}
                                            placeholder="Reason for exemption"
                                        />
                                        <InputError message={errors.tax_exempt_reason} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="referral">How did you hear about us?</Label>
                                    <Input
                                        id="referral"
                                        type="text"
                                        value={data.referral}
                                        onChange={e => setData('referral', e.target.value)}
                                        placeholder="Referral source"
                                    />
                                    <InputError message={errors.referral} />
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Step 2: Contact Information */}
                    {currentStep === 2 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Contact Information</CardTitle>
                                <CardDescription>
                                    How can we reach your business?
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-[1fr,auto]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">Primary Phone *</Label>
                                        <Input
                                            id="phone"
                                            type="tel"
                                            value={data.phone}
                                            onChange={e => setData('phone', e.target.value)}
                                            required
                                            placeholder="(555) 123-4567"
                                        />
                                        <InputError message={errors.phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="phone_ext">Ext</Label>
                                        <Input
                                            id="phone_ext"
                                            type="text"
                                            value={data.phone_ext}
                                            onChange={e => setData('phone_ext', e.target.value)}
                                            placeholder="123"
                                            className="w-20"
                                        />
                                        <InputError message={errors.phone_ext} />
                                    </div>
                                </div>

                                <div className="grid gap-4 md:grid-cols-[1fr,auto]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="secondary_phone">Secondary Phone</Label>
                                        <Input
                                            id="secondary_phone"
                                            type="tel"
                                            value={data.secondary_phone}
                                            onChange={e => setData('secondary_phone', e.target.value)}
                                            placeholder="(555) 987-6543"
                                        />
                                        <InputError message={errors.secondary_phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="secondary_phone_ext">Ext</Label>
                                        <Input
                                            id="secondary_phone_ext"
                                            type="text"
                                            value={data.secondary_phone_ext}
                                            onChange={e => setData('secondary_phone_ext', e.target.value)}
                                            placeholder="456"
                                            className="w-20"
                                        />
                                        <InputError message={errors.secondary_phone_ext} />
                                    </div>
                                </div>

                                <div className="grid gap-4 md:grid-cols-[1fr,auto]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="fax">Fax</Label>
                                        <Input
                                            id="fax"
                                            type="tel"
                                            value={data.fax}
                                            onChange={e => setData('fax', e.target.value)}
                                            placeholder="(555) 246-8135"
                                        />
                                        <InputError message={errors.fax} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="fax_ext">Ext</Label>
                                        <Input
                                            id="fax_ext"
                                            type="text"
                                            value={data.fax_ext}
                                            onChange={e => setData('fax_ext', e.target.value)}
                                            placeholder="789"
                                            className="w-20"
                                        />
                                        <InputError message={errors.fax_ext} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="delivery_method">Preferred Delivery Method</Label>
                                    <Select
                                        value={data.delivery_method}
                                        onValueChange={(value) => setData('delivery_method', value)}
                                    >
                                        <SelectTrigger id="delivery_method">
                                            <SelectValue placeholder="Select delivery method" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {deliveryMethods.map((method) => (
                                                <SelectItem key={method} value={method}>
                                                    {method}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.delivery_method} />
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Step 3: Address Information */}
                    {currentStep === 3 && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Service Address</CardTitle>
                                <CardDescription>
                                    Where should we provide services?
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="address">Primary Service Address *</Label>
                                    <Input
                                        id="address"
                                        type="text"
                                        value={data.address}
                                        onChange={e => setData('address', e.target.value)}
                                        required
                                        placeholder="123 Main Street"
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="secondary_address">Secondary/Billing Address</Label>
                                    <Input
                                        id="secondary_address"
                                        type="text"
                                        value={data.secondary_address}
                                        onChange={e => setData('secondary_address', e.target.value)}
                                        placeholder="Different billing address if applicable"
                                    />
                                    <InputError message={errors.secondary_address} />
                                </div>

                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="grid gap-2">
                                        <Label htmlFor="city">City *</Label>
                                        <Input
                                            id="city"
                                            type="text"
                                            value={data.city}
                                            onChange={e => setData('city', e.target.value)}
                                            required
                                            placeholder="Houston"
                                        />
                                        <InputError message={errors.city} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="state">State *</Label>
                                        <Select
                                            value={data.state}
                                            onValueChange={(value) => setData('state', value)}
                                        >
                                            <SelectTrigger id="state">
                                                <SelectValue placeholder="Select state" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {states.map((state) => (
                                                    <SelectItem key={state} value={state}>
                                                        {state}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.state} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="zip">ZIP Code *</Label>
                                        <Input
                                            id="zip"
                                            type="text"
                                            value={data.zip}
                                            onChange={e => setData('zip', e.target.value)}
                                            required
                                            placeholder="77001"
                                        />
                                        <InputError message={errors.zip} />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="county">County</Label>
                                    <Input
                                        id="county"
                                        type="text"
                                        value={data.county}
                                        onChange={e => setData('county', e.target.value)}
                                        placeholder="Harris County"
                                    />
                                    <InputError message={errors.county} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="internal_memo">Internal Notes</Label>
                                    <Textarea
                                        id="internal_memo"
                                        value={data.internal_memo}
                                        onChange={e => setData('internal_memo', e.target.value)}
                                        placeholder="Any special instructions or notes for service"
                                        rows={3}
                                    />
                                    <InputError message={errors.internal_memo} />
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Navigation Buttons */}
                    <div className="flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handlePrevious}
                            disabled={currentStep === 1}
                        >
                            Previous
                        </Button>

                        {currentStep < totalSteps ? (
                            <Button
                                type="button"
                                className={brandColor}
                                onClick={handleNext}
                            >
                                Next Step
                            </Button>
                        ) : (
                            <Button
                                type="submit"
                                className={brandColor}
                                disabled={processing}
                            >
                                {processing ? (
                                    <LoaderCircle className="h-4 w-4 animate-spin" />
                                ) : (
                                    <Check className="h-4 w-4" />
                                )}
                                Complete Setup
                            </Button>
                        )}
                    </div>
                </form>
            </div>
        </AuthCardLayout>
    );
}