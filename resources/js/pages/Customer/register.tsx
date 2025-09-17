import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, Shield, UserPlus } from 'lucide-react';
import { FormEventHandler } from 'react';

interface RegisterProps {
    invite: {
        email: string;
        token: string;
        customer_name: string | null;
        company_name: string;
        expires_at: string;
    };
}

export default function CustomerRegister({ invite }: RegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: invite.token,
        first_name: '',
        last_name: '',
        phone: '',
        phone_ext: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('customer.register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthCardLayout
            title="Create Your Customer Portal Account"
            description={`Complete your registration to access your customer portal for ${invite.company_name}.`}
        >
            <Head title="Customer Registration" />

            {/* Invitation Info */}
            <div className="mb-6 rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                <div className="flex items-start">
                    <div className="flex-shrink-0">
                        <Shield className="h-5 w-5 text-green-400" aria-hidden="true" />
                    </div>
                    <div className="ml-3">
                        <h3 className="text-sm font-medium text-green-800 dark:text-green-200">
                            You're invited!
                        </h3>
                        <div className="mt-2 text-sm text-green-700 dark:text-green-300">
                            <p>
                                {invite.customer_name ? (
                                    <>Welcome, {invite.customer_name}! </>
                                ) : (
                                    'Welcome! '
                                )}
                                You've been invited to create a customer portal account for{' '}
                                <strong>{invite.company_name}</strong>.
                            </p>
                            <p className="mt-1">
                                <strong>Email:</strong> {invite.email}
                            </p>
                            <p className="mt-1 text-xs">
                                This invitation expires on {invite.expires_at}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form onSubmit={submit} className="space-y-6">
                {/* Name Fields */}
                <div className="grid grid-cols-2 gap-4">
                    <div className="grid gap-2">
                        <Label htmlFor="first_name">First Name</Label>
                        <Input
                            id="first_name"
                            name="first_name"
                            value={data.first_name}
                            onChange={(e) => setData('first_name', e.target.value)}
                            required
                            autoFocus
                        />
                        <InputError message={errors.first_name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="last_name">Last Name</Label>
                        <Input
                            id="last_name"
                            name="last_name"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                            required
                        />
                        <InputError message={errors.last_name} />
                    </div>
                </div>

                {/* Phone Fields */}
                <div className="grid grid-cols-3 gap-4">
                    <div className="col-span-2 grid gap-2">
                        <Label htmlFor="phone">Phone Number (optional)</Label>
                        <Input
                            id="phone"
                            name="phone"
                            type="tel"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            placeholder="(555) 123-4567"
                        />
                        <InputError message={errors.phone} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone_ext">Ext.</Label>
                        <Input
                            id="phone_ext"
                            name="phone_ext"
                            value={data.phone_ext}
                            onChange={(e) => setData('phone_ext', e.target.value)}
                            placeholder="123"
                        />
                        <InputError message={errors.phone_ext} />
                    </div>
                </div>

                {/* Password Fields */}
                <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <div className="text-xs text-muted-foreground">
                        Password must be at least 8 characters with uppercase, lowercase, and numbers.
                    </div>
                    <InputError message={errors.password} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                {/* Security Notice */}
                <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <Shield className="h-5 w-5 text-blue-400" aria-hidden="true" />
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Secure Portal Access
                            </h3>
                            <div className="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <p>
                                    Your customer portal provides secure access to view service requests,
                                    invoices, schedules, and communicate with our team.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Submit Button */}
                <Button type="submit" className="w-full" disabled={processing}>
                    {processing ? (
                        <LoaderCircle className="h-4 w-4 animate-spin" />
                    ) : (
                        <UserPlus className="h-4 w-4" />
                    )}
                    Create Account & Sign In
                </Button>

                {/* Global Errors */}
                {errors.error && <InputError message={errors.error} />}
                {errors.token && <InputError message={errors.token} />}
            </form>

            {/* Footer */}
            <div className="mt-6 text-center text-sm text-muted-foreground">
                <p>
                    By creating an account, you agree to our terms of service and privacy policy.
                </p>
                <p className="mt-2">
                    Questions? Contact {invite.company_name} support for assistance.
                </p>
            </div>
        </AuthCardLayout>
    );
}