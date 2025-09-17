import { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, router } from '@inertiajs/react';
import { 
    ArrowLeft,
    Bell,
    BellOff,
    Check,
    CheckCheck,
    Filter,
    Search,
    Trash2,
    LogOut,
    Building2,
    AlertCircle,
    Info,
    DollarSign,
    FileText,
    Shield,
    Megaphone
} from 'lucide-react';

interface Notification {
    id: number;
    title: string;
    message: string;
    category: string;
    category_label: string;
    type: string;
    type_label: string;
    status: string;
    status_label: string;
    priority: string;
    data: any;
    is_read: boolean;
    read_at: string | null;
    formatted_read_at: string | null;
    created_at: string;
    formatted_created_at: string;
    time_ago: string;
    action_url: string | null;
}

interface NotificationsProps {
    customer: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        name: string;
    };
    notifications: Notification[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    summary: {
        total_count: number;
        unread_count: number;
        read_count: number;
    };
    filters: {
        status: string;
        category: string;
        type: string;
        search: string;
        date_from: string;
        date_to: string;
        sort_by: string;
        sort_order: string;
    };
}

export default function CustomerNotifications({ customer, company, notifications, pagination, summary, filters }: NotificationsProps) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [category, setCategory] = useState(filters.category);
    const [type, setType] = useState(filters.type);
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);

    const handleLogout = () => {
        router.post(route('customer.logout'));
    };

    const applyFilters = useCallback(() => {
        router.get(route('customer.dashboard'), {
            status,
            category,
            type,
            search,
            date_from: dateFrom,
            date_to: dateTo,
        }, { preserveState: true });
    }, [status, category, type, search, dateFrom, dateTo]);

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        setCategory('all');
        setType('all');
        setDateFrom('');
        setDateTo('');
        router.get(route('customer.dashboard'), {}, { preserveState: true });
    };

    const markAsRead = async (notificationId: number) => {
        try {
            await router.patch(`/api/customer/notifications/${notificationId}/read`, {}, {
                preserveState: true,
                preserveScroll: true,
            });
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await router.patch('/api/customer/notifications/mark-all-read', {}, {
                preserveState: true,
                preserveScroll: true,
            });
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    };

    const deleteNotification = async (notificationId: number) => {
        if (confirm('Are you sure you want to delete this notification?')) {
            try {
                await router.delete(`/api/customer/notifications/${notificationId}`, {
                    preserveState: true,
                    preserveScroll: true,
                });
            } catch (error) {
                console.error('Failed to delete notification:', error);
            }
        }
    };

    const getCategoryIcon = (category: string) => {
        switch (category) {
            case 'invoice':
                return <DollarSign className="h-4 w-4" />;
            case 'service_request':
                return <FileText className="h-4 w-4" />;
            case 'security':
                return <Shield className="h-4 w-4" />;
            case 'marketing':
                return <Megaphone className="h-4 w-4" />;
            case 'system':
                return <Info className="h-4 w-4" />;
            default:
                return <Bell className="h-4 w-4" />;
        }
    };

    const getCategoryColor = (category: string) => {
        switch (category) {
            case 'invoice':
                return 'text-green-600';
            case 'service_request':
                return 'text-blue-600';
            case 'security':
                return 'text-red-600';
            case 'marketing':
                return 'text-purple-600';
            case 'system':
                return 'text-gray-600';
            default:
                return 'text-gray-600';
        }
    };

    const getPriorityBadge = (priority: string) => {
        const variant = priority === 'high' ? 'destructive' :
                      priority === 'medium' ? 'secondary' : 'outline';
        
        return <Badge variant={variant}>{priority}</Badge>;
    };

    return (
        <div className="min-h-screen bg-background">
            <Head title="Notifications" />
            
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
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <Link href={route('customer.dashboard')}>
                                <Button variant="ghost" size="sm">
                                    <ArrowLeft className="h-4 w-4" />
                                    Back to Dashboard
                                </Button>
                            </Link>
                            <div>
                                <h2 className="text-2xl font-bold tracking-tight">Notifications</h2>
                                <p className="text-muted-foreground">
                                    Stay updated with your account activity and important messages
                                </p>
                            </div>
                        </div>
                        
                        {summary.unread_count > 0 && (
                            <Button onClick={markAllAsRead}>
                                <CheckCheck className="h-4 w-4 mr-2" />
                                Mark All Read
                            </Button>
                        )}
                    </div>

                    {/* Summary Stats */}
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Notifications</CardTitle>
                                <Bell className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{summary.total_count}</div>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Unread</CardTitle>
                                <BellOff className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-orange-600">{summary.unread_count}</div>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Read</CardTitle>
                                <Check className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{summary.read_count}</div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Filters */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Filter className="h-5 w-5 mr-2" />
                                Filter Notifications
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Search</label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search notifications..."
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Status</label>
                                    <Select value={status} onValueChange={setStatus}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All statuses" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All</SelectItem>
                                            <SelectItem value="unread">Unread</SelectItem>
                                            <SelectItem value="read">Read</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Category</label>
                                    <Select value={category} onValueChange={setCategory}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All categories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Categories</SelectItem>
                                            <SelectItem value="invoice">Invoices</SelectItem>
                                            <SelectItem value="service_request">Service Requests</SelectItem>
                                            <SelectItem value="security">Security</SelectItem>
                                            <SelectItem value="system">System</SelectItem>
                                            <SelectItem value="marketing">Marketing</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <label className="text-sm font-medium mb-2 block">From Date</label>
                                    <Input
                                        type="date"
                                        value={dateFrom}
                                        onChange={(e) => setDateFrom(e.target.value)}
                                    />
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium mb-2 block">To Date</label>
                                    <Input
                                        type="date"
                                        value={dateTo}
                                        onChange={(e) => setDateTo(e.target.value)}
                                    />
                                </div>
                                
                                <div className="flex items-end space-x-2">
                                    <Button onClick={applyFilters} className="flex-1">
                                        Apply Filters
                                    </Button>
                                    <Button variant="outline" onClick={clearFilters}>
                                        Clear
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notifications List */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>
                                    Notifications ({pagination.total})
                                </CardTitle>
                                <div className="text-sm text-muted-foreground">
                                    Showing {pagination.from}-{pagination.to} of {pagination.total} notifications
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {notifications.length > 0 ? (
                                <div className="space-y-4">
                                    {notifications.map((notification) => (
                                        <div 
                                            key={notification.id} 
                                            className={`border rounded-lg p-4 hover:bg-muted/50 ${
                                                !notification.is_read ? 'bg-blue-50 border-blue-200' : ''
                                            }`}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 space-y-2">
                                                    <div className="flex items-center space-x-3">
                                                        <div className={`flex items-center space-x-2 ${getCategoryColor(notification.category)}`}>
                                                            {getCategoryIcon(notification.category)}
                                                            <span className="text-sm font-medium">{notification.category_label}</span>
                                                        </div>
                                                        
                                                        {!notification.is_read && (
                                                            <Badge variant="secondary">New</Badge>
                                                        )}
                                                        
                                                        {notification.priority !== 'low' && (
                                                            getPriorityBadge(notification.priority)
                                                        )}
                                                    </div>
                                                    
                                                    <h3 className="font-semibold">{notification.title}</h3>
                                                    <p className="text-muted-foreground">{notification.message}</p>
                                                    
                                                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                                                        <span>{notification.time_ago}</span>
                                                        {notification.is_read && notification.formatted_read_at && (
                                                            <span className="text-xs">Read {notification.formatted_read_at}</span>
                                                        )}
                                                    </div>
                                                </div>
                                                
                                                <div className="flex items-center space-x-2 ml-4">
                                                    {!notification.is_read && (
                                                        <Button 
                                                            size="sm" 
                                                            variant="outline"
                                                            onClick={() => markAsRead(notification.id)}
                                                        >
                                                            <Check className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                    
                                                    {notification.action_url && (
                                                        <Link href={notification.action_url}>
                                                            <Button size="sm" variant="outline">
                                                                View
                                                            </Button>
                                                        </Link>
                                                    )}
                                                    
                                                    <Button 
                                                        size="sm" 
                                                        variant="outline"
                                                        onClick={() => deleteNotification(notification.id)}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-600" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <Bell className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <h3 className="mt-2 text-sm font-semibold text-foreground">
                                        No notifications found
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {Object.values(filters).some(v => v && v !== 'all') 
                                            ? 'Try adjusting your filters to see more results.'
                                            : 'You\'re all caught up! New notifications will appear here.'
                                        }
                                    </p>
                                    {Object.values(filters).some(v => v && v !== 'all') && (
                                        <div className="mt-6">
                                            <Button variant="outline" onClick={clearFilters}>
                                                Clear Filters
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Pagination */}
                            {pagination.last_page > 1 && (
                                <div className="flex items-center justify-between pt-6 border-t">
                                    <div className="text-sm text-muted-foreground">
                                        Page {pagination.current_page} of {pagination.last_page}
                                    </div>
                                    <div className="flex space-x-2">
                                        {pagination.current_page > 1 && (
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => router.get(route('customer.dashboard'), { 
                                                    ...filters, 
                                                    page: pagination.current_page - 1 
                                                })}
                                            >
                                                Previous
                                            </Button>
                                        )}
                                        {pagination.current_page < pagination.last_page && (
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => router.get(route('customer.dashboard'), { 
                                                    ...filters, 
                                                    page: pagination.current_page + 1 
                                                })}
                                            >
                                                Next
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </main>
        </div>
    );
}