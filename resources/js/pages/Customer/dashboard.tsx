import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/react';
import { 
    AlertCircle,
    Bell,
    Building2, 
    Calendar, 
    DollarSign,
    FileText, 
    LogOut, 
    Mail, 
    MapPin, 
    Phone, 
    Plus, 
    Settings,
    TrendingUp,
    User 
} from 'lucide-react';

interface DashboardProps {
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        address: string | null;
        city: string | null;
        state: string | null;
        zip: string | null;
        customer_number: string | null;
        customer_since: string | null;
    };
    company: {
        name: string;
        phone: string | null;
        email: string | null;
    };
    recent_service_requests: Array<{
        id: number;
        title: string;
        status: string;
        status_label: string;
        priority: string;
        created_at: string;
    }>;
    recent_invoices: Array<{
        id: number;
        invoice_number: string;
        total_amount: string;
        status: string;
        due_date: string;
        is_overdue: boolean;
    }>;
    stats: {
        unread_notifications: number;
        active_service_requests: number;
        pending_invoices: number;
    };
}

export default function CustomerDashboard({ customer, company, recent_service_requests, recent_invoices, stats }: DashboardProps) {
    const handleLogout = () => {
        router.post(route('customer.logout'));
    };

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'default';
            case 'in_progress':
                return 'secondary';
            case 'pending':
                return 'outline';
            default:
                return 'secondary';
        }
    };

    return (
        <div className="min-h-screen bg-background">
            <Head title="Customer Dashboard" />
            
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
                <div className="space-y-8">
                    {/* Welcome Section */}
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">
                            Welcome back, {customer.name.split(' ')[0]}!
                        </h2>
                        <p className="text-muted-foreground">
                            Manage your account and view your service history with {company.name}.
                        </p>
                    </div>

                    {/* Key Metrics */}
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Active Service Requests</CardTitle>
                                <FileText className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats.active_service_requests}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    In progress or pending
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Pending Invoices</CardTitle>
                                <DollarSign className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats.pending_invoices}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Awaiting payment
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Notifications</CardTitle>
                                <Bell className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {stats.unread_notifications}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Unread messages
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Customer Since</CardTitle>
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {customer.customer_since || 'N/A'}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Member since
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Main Grid */}
                    <div className="grid gap-8 lg:grid-cols-2">
                        {/* Recent Service Requests */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Recent Service Requests</CardTitle>
                                        <CardDescription>
                                            Your latest service requests and their status
                                        </CardDescription>
                                    </div>
                                    <Button size="sm">
                                        <Plus className="h-4 w-4" />
                                        New Request
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recent_service_requests.length > 0 ? (
                                    <div className="space-y-4">
                                        {recent_service_requests.map((request) => (
                                            <div
                                                key={request.id}
                                                className="flex items-center justify-between rounded-lg border p-4"
                                            >
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none">
                                                        {request.title}
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        Created on {request.created_at} • Priority: {request.priority}
                                                    </p>
                                                </div>
                                                <Badge variant={getStatusColor(request.status)}>
                                                    {request.status_label}
                                                </Badge>
                                            </div>
                                        ))}
                                        
                                        <Button variant="outline" className="w-full">
                                            View All Service Requests
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <FileText className="mx-auto h-12 w-12 text-muted-foreground" />
                                        <h3 className="mt-2 text-sm font-semibold text-foreground">
                                            No service requests
                                        </h3>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Get started by creating your first service request.
                                        </p>
                                        <div className="mt-6">
                                            <Button>
                                                <Plus className="h-4 w-4" />
                                                New Service Request
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Recent Invoices */}
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle>Recent Invoices</CardTitle>
                                        <CardDescription>
                                            Your latest invoices and payment status
                                        </CardDescription>
                                    </div>
                                    <Button size="sm" variant="outline">
                                        <DollarSign className="h-4 w-4" />
                                        View All
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recent_invoices.length > 0 ? (
                                    <div className="space-y-4">
                                        {recent_invoices.map((invoice) => (
                                            <div
                                                key={invoice.id}
                                                className="flex items-center justify-between rounded-lg border p-4"
                                            >
                                                <div className="space-y-1">
                                                    <p className="text-sm font-medium leading-none">
                                                        Invoice #{invoice.invoice_number}
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        Due {invoice.due_date} • ${invoice.total_amount}
                                                    </p>
                                                </div>
                                                <div className="flex items-center space-x-2">
                                                    {invoice.is_overdue && (
                                                        <AlertCircle className="h-4 w-4 text-red-500" />
                                                    )}
                                                    <Badge variant={invoice.status === 'paid' ? 'default' : invoice.is_overdue ? 'destructive' : 'secondary'}>
                                                        {invoice.status}
                                                    </Badge>
                                                </div>
                                            </div>
                                        ))}
                                        
                                        <Button variant="outline" className="w-full">
                                            View All Invoices
                                        </Button>
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <DollarSign className="mx-auto h-12 w-12 text-muted-foreground" />
                                        <h3 className="mt-2 text-sm font-semibold text-foreground">
                                            No invoices
                                        </h3>
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            Your invoices will appear here once services are provided.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Common tasks and services you can access
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                                <Button variant="outline" className="h-auto flex-col space-y-2 p-6">
                                    <Plus className="h-6 w-6" />
                                    <span>New Service Request</span>
                                </Button>
                                
                                <Button variant="outline" className="h-auto flex-col space-y-2 p-6 relative">
                                    <DollarSign className="h-6 w-6" />
                                    <span>View Invoices</span>
                                    {stats.pending_invoices > 0 && (
                                        <Badge className="absolute -top-2 -right-2 h-5 w-5 p-0 flex items-center justify-center text-xs">
                                            {stats.pending_invoices}
                                        </Badge>
                                    )}
                                </Button>
                                
                                <Button variant="outline" className="h-auto flex-col space-y-2 p-6 relative">
                                    <FileText className="h-6 w-6" />
                                    <span>Service Requests</span>
                                    {stats.active_service_requests > 0 && (
                                        <Badge className="absolute -top-2 -right-2 h-5 w-5 p-0 flex items-center justify-center text-xs">
                                            {stats.active_service_requests}
                                        </Badge>
                                    )}
                                </Button>
                                
                                <Button variant="outline" className="h-auto flex-col space-y-2 p-6 relative">
                                    <Bell className="h-6 w-6" />
                                    <span>Notifications</span>
                                    {stats.unread_notifications > 0 && (
                                        <Badge className="absolute -top-2 -right-2 h-5 w-5 p-0 flex items-center justify-center text-xs">
                                            {stats.unread_notifications}
                                        </Badge>
                                    )}
                                </Button>
                                
                                <Button variant="outline" className="h-auto flex-col space-y-2 p-6">
                                    <Settings className="h-6 w-6" />
                                    <span>Account Settings</span>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </main>
        </div>
    );
}