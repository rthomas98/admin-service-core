import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthCardLayout from '@/layouts/auth/auth-card-layout';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, LogIn } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function CustomerLogin() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('customer.login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthCardLayout
            title="Customer Portal Sign In"
            description="Access your customer account to view orders, invoices, and more."
        >
            <Head title="Customer Sign In" />

            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-2">
                    <Label htmlFor="email">Email Address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoFocus
                        autoComplete="username"
                    />
                    <InputError message={errors.email} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                        autoComplete="current-password"
                    />
                    <InputError message={errors.password} />
                </div>

                <div className="flex items-center space-x-2">
                    <Checkbox
                        id="remember"
                        checked={data.remember}
                        onCheckedChange={(checked) => setData('remember', checked as boolean)}
                    />
                    <Label htmlFor="remember" className="text-sm font-normal">
                        Remember me
                    </Label>
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    {processing ? (
                        <LoaderCircle className="h-4 w-4 animate-spin" />
                    ) : (
                        <LogIn className="h-4 w-4" />
                    )}
                    Sign In
                </Button>
            </form>

            <div className="mt-6 text-center text-sm text-muted-foreground">
                <p>
                    Don't have an account yet? Contact us to request portal access.
                </p>
                <p className="mt-2">
                    <a
                        href="/forgot-password"
                        className="text-primary underline-offset-4 hover:underline"
                    >
                        Forgot your password?
                    </a>
                </p>
            </div>
        </AuthCardLayout>
    );
}