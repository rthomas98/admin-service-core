import { FormEventHandler, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Users, Building2, Clock, User } from 'lucide-react';

interface TeamInviteData {
    token: string;
    email: string;
    name: string | null;
    role: string;
    company: string | null;
    invitedBy: string;
    expiresAt: string;
}

interface Props {
    invite: TeamInviteData;
}

export default function TeamRegister({ invite }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: invite.token,
        name: invite.name || '',
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('team.register.store'));
    };

    const expiresIn = () => {
        const expiresAt = new Date(invite.expiresAt);
        const now = new Date();
        const diff = expiresAt.getTime() - now.getTime();
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

        if (days > 0) {
            return `${days} day${days > 1 ? 's' : ''}`;
        } else if (hours > 0) {
            return `${hours} hour${hours > 1 ? 's' : ''}`;
        } else {
            return 'soon';
        }
    };

    return (
        <>
            <Head title="Join the Team" />

            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="flex justify-center">
                        <div className="bg-blue-600 p-3 rounded-lg">
                            <Users className="h-12 w-12 text-white" />
                        </div>
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Join the Team
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        You've been invited by {invite.invitedBy}
                    </p>
                </div>

                <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                    <Card>
                        <CardHeader className="space-y-4">
                            <div className="space-y-2">
                                <CardTitle>Complete Your Registration</CardTitle>
                                <CardDescription>
                                    Set up your account to join {invite.company || 'the team'}
                                </CardDescription>
                            </div>

                            <div className="space-y-3">
                                <div className="flex items-center space-x-2">
                                    <User className="h-4 w-4 text-gray-500" />
                                    <span className="text-sm text-gray-600">Role:</span>
                                    <Badge variant="secondary">{invite.role}</Badge>
                                </div>

                                {invite.company && (
                                    <div className="flex items-center space-x-2">
                                        <Building2 className="h-4 w-4 text-gray-500" />
                                        <span className="text-sm text-gray-600">Company:</span>
                                        <span className="text-sm font-medium">{invite.company}</span>
                                    </div>
                                )}

                                <div className="flex items-center space-x-2">
                                    <Clock className="h-4 w-4 text-gray-500" />
                                    <span className="text-sm text-gray-600">
                                        Expires in {expiresIn()}
                                    </span>
                                </div>
                            </div>
                        </CardHeader>

                        <form onSubmit={submit}>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={invite.email}
                                        disabled
                                        className="bg-gray-50"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoFocus
                                        placeholder="Enter your full name"
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-600">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        placeholder="Create a strong password"
                                    />
                                    {errors.password && (
                                        <p className="text-sm text-red-600">{errors.password}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">Confirm Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        required
                                        placeholder="Confirm your password"
                                    />
                                    {errors.password_confirmation && (
                                        <p className="text-sm text-red-600">{errors.password_confirmation}</p>
                                    )}
                                </div>

                                {errors.token && (
                                    <Alert variant="destructive">
                                        <AlertDescription>{errors.token}</AlertDescription>
                                    </Alert>
                                )}
                            </CardContent>

                            <CardFooter className="flex flex-col space-y-4">
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing ? 'Creating Account...' : 'Create Account & Join Team'}
                                </Button>

                                <p className="text-center text-sm text-gray-600">
                                    Already have an account?{' '}
                                    <a
                                        href="/login"
                                        className="font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        Sign in instead
                                    </a>
                                </p>
                            </CardFooter>
                        </form>
                    </Card>
                </div>
            </div>
        </>
    );
}