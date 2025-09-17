import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, UserPlus } from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';

interface Props {
    invite: {
        token: string;
        email: string;
        name: string;
        company: string;
        role: string;
    };
}

export default function AcceptInvite({ invite }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: invite.name || '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/company-portal/accept-invite/${invite.token}`);
    };

    return (
        <AuthCardLayout 
            title="Accept Company Invitation" 
            description={`Complete your registration to join ${invite.company}`}
        >
            <Head title="Accept Invitation - Service Core" />
            
            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Company Info Card */}
                <Card className="border-primary/20 bg-primary/5">
                    <div className="p-4 space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-medium text-muted-foreground">Organization</span>
                            <span className="font-semibold text-foreground">{invite.company}</span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-medium text-muted-foreground">Your Role</span>
                            <Badge
                                variant={invite.role === 'company' ? 'default' : 'secondary'}
                                className="capitalize"
                            >
                                {invite.role === 'company' ? 'Company Owner' : invite.role}
                            </Badge>
                        </div>
                    </div>
                </Card>

                {/* Email Field (Disabled) */}
                <div className="grid gap-2">
                    <Label htmlFor="email">Email Address</Label>
                    <Input
                        id="email"
                        type="email"
                        value={invite.email}
                        disabled
                        className="bg-muted/50 text-muted-foreground"
                    />
                    <p className="text-xs text-muted-foreground">
                        This email address cannot be changed
                    </p>
                </div>

                {/* Name Field */}
                <div className="grid gap-2">
                    <Label htmlFor="name">Full Name</Label>
                    <Input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={e => setData('name', e.target.value)}
                        required
                        autoFocus
                        autoComplete="name"
                        placeholder="Enter your full name"
                    />
                    <InputError message={errors.name} />
                </div>

                {/* Password Field */}
                <div className="grid gap-2">
                    <Label htmlFor="password">Create Password</Label>
                    <Input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        required
                        autoComplete="new-password"
                        placeholder="Choose a strong password"
                    />
                    <InputError message={errors.password} />
                    <p className="text-xs text-muted-foreground">
                        Must be at least 8 characters long
                    </p>
                </div>

                {/* Confirm Password Field */}
                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={e => setData('password_confirmation', e.target.value)}
                        required
                        autoComplete="new-password"
                        placeholder="Re-enter your password"
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                {/* Submit Button */}
                <Button 
                    type="submit" 
                    className="w-full bg-primary hover:bg-primary/90" 
                    disabled={processing}
                >
                    {processing ? (
                        <LoaderCircle className="h-4 w-4 animate-spin" />
                    ) : (
                        <UserPlus className="h-4 w-4" />
                    )}
                    Complete Registration
                </Button>

                {/* Footer Text */}
                <div className="text-center text-sm text-muted-foreground">
                    <p>
                        By accepting this invitation, you agree to our{' '}
                        <a href="/terms" className="text-primary underline-offset-4 hover:underline">
                            Terms of Service
                        </a>{' '}
                        and{' '}
                        <a href="/privacy" className="text-primary underline-offset-4 hover:underline">
                            Privacy Policy
                        </a>
                    </p>
                </div>
            </form>
        </AuthCardLayout>
    );
}