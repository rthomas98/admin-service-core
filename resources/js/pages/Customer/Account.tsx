import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { 
    ArrowLeft,
    Building2,
    Check,
    Eye,
    EyeOff,
    LogOut,
    Mail,
    MapPin,
    Phone,
    Save,
    Settings,
    Shield,
    User
} from 'lucide-react';

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    name: string;
    full_name: string;
    organization: string;
    customer_number: string;
    customer_since: string;
    emails: string[];
    primary_email: string;
    phone: string;
    phone_ext: string;
    secondary_phone: string;
    secondary_phone_ext: string;
    fax: string;
    fax_ext: string;
    address: string;
    secondary_address: string;
    city: string;
    state: string;
    zip: string;
    county: string;
    business_type: string;
    tax_exempt_reason: string;
    delivery_method: string;
    referral: string;
}

interface NotificationSettings {
    notifications_enabled: boolean;
    preferred_method: string;
    sms_number: string;
    sms_verified: boolean;
    preferences: Record<string, any>;
}

interface AccountProps {
    customer: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        name: string;
        address: string;
        city: string;
        state: string;
        zip: string;
        phone: string;
        email: string;
    };
    profile: Customer;
    notification_settings: NotificationSettings;
}

export default function CustomerAccount({ customer, company, profile, notification_settings }: AccountProps) {
    const [activeTab, setActiveTab] = useState('profile');
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const handleLogout = () => {
        router.post(route('customer.logout'));
    };

    // Profile form
    const profileForm = useForm({
        first_name: profile.first_name || '',
        last_name: profile.last_name || '',
        name: profile.name || '',
        organization: profile.organization || '',
        phone: profile.phone || '',
        phone_ext: profile.phone_ext || '',
        secondary_phone: profile.secondary_phone || '',
        secondary_phone_ext: profile.secondary_phone_ext || '',
        fax: profile.fax || '',
        fax_ext: profile.fax_ext || '',
        address: profile.address || '',
        secondary_address: profile.secondary_address || '',
        city: profile.city || '',
        state: profile.state || '',
        zip: profile.zip || '',
        county: profile.county || '',
        business_type: profile.business_type || '',
        delivery_method: profile.delivery_method || '',
        referral: profile.referral || '',
    });

    // Password form
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    // Notification form
    const notificationForm = useForm({
        notifications_enabled: notification_settings.notifications_enabled,
        preferred_method: notification_settings.preferred_method || 'email',
        sms_number: notification_settings.sms_number || '',
        preferences: notification_settings.preferences || {},
    });

    const updateProfile = (e: React.FormEvent) => {
        e.preventDefault();
        profileForm.patch('/api/customer/account/profile', {
            onSuccess: () => {
                // Handle success
            },
        });
    };

    const updatePassword = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.patch('/api/customer/account/password', {
            onSuccess: () => {
                passwordForm.reset();
                // Handle success
            },
        });
    };

    const updateNotifications = (e: React.FormEvent) => {
        e.preventDefault();
        notificationForm.patch('/api/customer/account/notifications', {
            onSuccess: () => {
                // Handle success
            },
        });
    };

    const tabs = [
        { id: 'profile', label: 'Profile Information', icon: User },
        { id: 'password', label: 'Password & Security', icon: Shield },
        { id: 'notifications', label: 'Notifications', icon: Mail },
        { id: 'company', label: 'Company Information', icon: Building2 },
    ];

    return (
        <div className="min-h-screen bg-background">
            <Head title="Account Settings" />
            
            {/* Header */}
            <header className="border-b bg-card">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 items-center justify-between">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <Building2 className="h-8 w-8 text-primary" />
                            </div>
                            <div className="ml-4">
                                <h1 className="text-lg font-semibold">{company.name}</h1>
                                <p className="text-sm text-muted-foreground">Customer Portal</p>
                            </div>
                        </div>
                        
                        <div className="flex items-center space-x-4">
                            <div className="text-right">
                                <p className="text-sm font-medium">{customer.name}</p>
                                <p className="text-xs text-muted-foreground">{customer.email}</p>
                            </div>
                            <Button variant="ghost" size="sm" onClick={handleLogout}>
                                <LogOut className="h-4 w-4" />
                                Sign Out
                            </Button>
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="space-y-6">
                    {/* Page Header */}
                    <div className="flex items-center space-x-4">
                        <Link href={route('customer.dashboard')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4" />
                                Back to Dashboard
                            </Button>
                        </Link>
                        <div>
                            <h2 className="text-2xl font-bold tracking-tight">Account Settings</h2>
                            <p className="text-muted-foreground">
                                Manage your account information and preferences
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-8 lg:grid-cols-4">
                        {/* Sidebar Navigation */}
                        <div className="lg:col-span-1">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center">
                                        <Settings className="h-5 w-5 mr-2" />
                                        Settings
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <nav className="space-y-1">
                                        {tabs.map((tab) => {
                                            const Icon = tab.icon;
                                            return (
                                                <button
                                                    key={tab.id}
                                                    onClick={() => setActiveTab(tab.id)}
                                                    className={`w-full flex items-center px-4 py-3 text-sm font-medium text-left hover:bg-muted transition-colors ${
                                                        activeTab === tab.id ? 'bg-muted border-r-2 border-primary' : ''
                                                    }`}
                                                >
                                                    <Icon className="h-4 w-4 mr-3" />
                                                    {tab.label}
                                                </button>
                                            );
                                        })}
                                    </nav>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Content Area */}
                        <div className="lg:col-span-3">
                            {/* Profile Information Tab */}
                            {activeTab === 'profile' && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Profile Information</CardTitle>
                                        <CardDescription>
                                            Update your personal and contact information
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={updateProfile} className="space-y-6">
                                            {/* Basic Information */}
                                            <div className="grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <Label htmlFor="first_name">First Name</Label>
                                                    <Input
                                                        id="first_name"
                                                        value={profileForm.data.first_name}
                                                        onChange={(e) => profileForm.setData('first_name', e.target.value)}
                                                        error={profileForm.errors.first_name}
                                                    />
                                                </div>
                                                <div>
                                                    <Label htmlFor="last_name">Last Name</Label>
                                                    <Input
                                                        id="last_name"
                                                        value={profileForm.data.last_name}
                                                        onChange={(e) => profileForm.setData('last_name', e.target.value)}
                                                        error={profileForm.errors.last_name}
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <Label htmlFor="name">Display Name</Label>
                                                    <Input
                                                        id="name"
                                                        value={profileForm.data.name}
                                                        onChange={(e) => profileForm.setData('name', e.target.value)}
                                                        error={profileForm.errors.name}
                                                    />
                                                </div>
                                                <div>
                                                    <Label htmlFor="organization">Organization</Label>
                                                    <Input
                                                        id="organization"
                                                        value={profileForm.data.organization}
                                                        onChange={(e) => profileForm.setData('organization', e.target.value)}
                                                        error={profileForm.errors.organization}
                                                    />
                                                </div>
                                            </div>

                                            {/* Contact Information */}
                                            <div className="space-y-4">
                                                <h3 className="text-lg font-medium">Contact Information</h3>
                                                
                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <div className="md:col-span-2">
                                                        <Label htmlFor="phone">Primary Phone</Label>
                                                        <Input
                                                            id="phone"
                                                            value={profileForm.data.phone}
                                                            onChange={(e) => profileForm.setData('phone', e.target.value)}
                                                            error={profileForm.errors.phone}
                                                        />
                                                    </div>
                                                    <div>
                                                        <Label htmlFor="phone_ext">Extension</Label>
                                                        <Input
                                                            id="phone_ext"
                                                            value={profileForm.data.phone_ext}
                                                            onChange={(e) => profileForm.setData('phone_ext', e.target.value)}
                                                            error={profileForm.errors.phone_ext}
                                                        />
                                                    </div>
                                                </div>

                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <div className="md:col-span-2">
                                                        <Label htmlFor="secondary_phone">Secondary Phone</Label>
                                                        <Input
                                                            id="secondary_phone"
                                                            value={profileForm.data.secondary_phone}
                                                            onChange={(e) => profileForm.setData('secondary_phone', e.target.value)}
                                                            error={profileForm.errors.secondary_phone}
                                                        />
                                                    </div>
                                                    <div>
                                                        <Label htmlFor="secondary_phone_ext">Extension</Label>
                                                        <Input
                                                            id="secondary_phone_ext"
                                                            value={profileForm.data.secondary_phone_ext}
                                                            onChange={(e) => profileForm.setData('secondary_phone_ext', e.target.value)}
                                                            error={profileForm.errors.secondary_phone_ext}
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Address Information */}
                                            <div className="space-y-4">
                                                <h3 className="text-lg font-medium">Address Information</h3>
                                                
                                                <div>
                                                    <Label htmlFor="address">Primary Address</Label>
                                                    <Input
                                                        id="address"
                                                        value={profileForm.data.address}
                                                        onChange={(e) => profileForm.setData('address', e.target.value)}
                                                        error={profileForm.errors.address}
                                                    />
                                                </div>

                                                <div className="grid gap-4 md:grid-cols-3">
                                                    <div>
                                                        <Label htmlFor="city">City</Label>
                                                        <Input
                                                            id="city"
                                                            value={profileForm.data.city}
                                                            onChange={(e) => profileForm.setData('city', e.target.value)}
                                                            error={profileForm.errors.city}
                                                        />
                                                    </div>
                                                    <div>
                                                        <Label htmlFor="state">State</Label>
                                                        <Input
                                                            id="state"
                                                            value={profileForm.data.state}
                                                            onChange={(e) => profileForm.setData('state', e.target.value)}
                                                            error={profileForm.errors.state}
                                                        />
                                                    </div>
                                                    <div>
                                                        <Label htmlFor="zip">ZIP Code</Label>
                                                        <Input
                                                            id="zip"
                                                            value={profileForm.data.zip}
                                                            onChange={(e) => profileForm.setData('zip', e.target.value)}
                                                            error={profileForm.errors.zip}
                                                        />
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex justify-end">
                                                <Button type="submit" disabled={profileForm.processing}>
                                                    <Save className="h-4 w-4 mr-2" />
                                                    {profileForm.processing ? 'Saving...' : 'Save Changes'}
                                                </Button>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Password & Security Tab */}
                            {activeTab === 'password' && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Password & Security</CardTitle>
                                        <CardDescription>
                                            Update your password and security settings
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={updatePassword} className="space-y-6">
                                            <div>
                                                <Label htmlFor="current_password">Current Password</Label>
                                                <div className="relative">
                                                    <Input
                                                        id="current_password"
                                                        type={showPassword ? 'text' : 'password'}
                                                        value={passwordForm.data.current_password}
                                                        onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                                        error={passwordForm.errors.current_password}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowPassword(!showPassword)}
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                    >
                                                        {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                    </button>
                                                </div>
                                            </div>

                                            <div>
                                                <Label htmlFor="password">New Password</Label>
                                                <Input
                                                    id="password"
                                                    type="password"
                                                    value={passwordForm.data.password}
                                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                                    error={passwordForm.errors.password}
                                                />
                                            </div>

                                            <div>
                                                <Label htmlFor="password_confirmation">Confirm New Password</Label>
                                                <Input
                                                    id="password_confirmation"
                                                    type="password"
                                                    value={passwordForm.data.password_confirmation}
                                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                                    error={passwordForm.errors.password_confirmation}
                                                />
                                            </div>

                                            <div className="flex justify-end">
                                                <Button type="submit" disabled={passwordForm.processing}>
                                                    <Save className="h-4 w-4 mr-2" />
                                                    {passwordForm.processing ? 'Updating...' : 'Update Password'}
                                                </Button>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Notifications Tab */}
                            {activeTab === 'notifications' && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Notification Preferences</CardTitle>
                                        <CardDescription>
                                            Control how and when you receive notifications
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <form onSubmit={updateNotifications} className="space-y-6">
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <Label htmlFor="notifications_enabled">Enable Notifications</Label>
                                                    <p className="text-sm text-muted-foreground">
                                                        Receive notifications about your account activity
                                                    </p>
                                                </div>
                                                <Checkbox
                                                    id="notifications_enabled"
                                                    checked={notificationForm.data.notifications_enabled}
                                                    onCheckedChange={(checked) => notificationForm.setData('notifications_enabled', checked)}
                                                />
                                            </div>

                                            {notificationForm.data.notifications_enabled && (
                                                <>
                                                    <div>
                                                        <Label htmlFor="preferred_method">Preferred Method</Label>
                                                        <Select
                                                            value={notificationForm.data.preferred_method}
                                                            onValueChange={(value) => notificationForm.setData('preferred_method', value)}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="email">Email Only</SelectItem>
                                                                <SelectItem value="sms">SMS Only</SelectItem>
                                                                <SelectItem value="both">Email and SMS</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>

                                                    {(notificationForm.data.preferred_method === 'sms' || notificationForm.data.preferred_method === 'both') && (
                                                        <div>
                                                            <Label htmlFor="sms_number">SMS Number</Label>
                                                            <Input
                                                                id="sms_number"
                                                                type="tel"
                                                                value={notificationForm.data.sms_number}
                                                                onChange={(e) => notificationForm.setData('sms_number', e.target.value)}
                                                                placeholder="Enter phone number for SMS notifications"
                                                            />
                                                            {notification_settings.sms_verified ? (
                                                                <p className="text-sm text-green-600 flex items-center mt-1">
                                                                    <Check className="h-4 w-4 mr-1" />
                                                                    Phone number verified
                                                                </p>
                                                            ) : (
                                                                <p className="text-sm text-yellow-600 mt-1">
                                                                    Phone number needs verification
                                                                </p>
                                                            )}
                                                        </div>
                                                    )}
                                                </>
                                            )}

                                            <div className="flex justify-end">
                                                <Button type="submit" disabled={notificationForm.processing}>
                                                    <Save className="h-4 w-4 mr-2" />
                                                    {notificationForm.processing ? 'Saving...' : 'Save Preferences'}
                                                </Button>
                                            </div>
                                        </form>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Company Information Tab */}
                            {activeTab === 'company' && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Company Information</CardTitle>
                                        <CardDescription>
                                            Information about {company.name}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-6">
                                        <div className="grid gap-6 md:grid-cols-2">
                                            <div className="space-y-4">
                                                <h3 className="font-medium">Contact Information</h3>
                                                <div className="space-y-3">
                                                    <div className="flex items-center space-x-2">
                                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                                        <span>{company.name}</span>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                                        <span>{company.email}</span>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <Phone className="h-4 w-4 text-muted-foreground" />
                                                        <span>{company.phone}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="space-y-4">
                                                <h3 className="font-medium">Address</h3>
                                                <div className="flex items-start space-x-2">
                                                    <MapPin className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <div>{company.address}</div>
                                                        <div>{company.city}, {company.state} {company.zip}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="border-t pt-6">
                                            <h3 className="font-medium mb-4">Your Account Details</h3>
                                            <div className="grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <span className="text-sm font-medium">Customer Number:</span>
                                                    <div className="text-muted-foreground">{profile.customer_number}</div>
                                                </div>
                                                <div>
                                                    <span className="text-sm font-medium">Customer Since:</span>
                                                    <div className="text-muted-foreground">{profile.customer_since}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}