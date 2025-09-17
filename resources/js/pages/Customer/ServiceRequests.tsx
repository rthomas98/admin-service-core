import { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, router } from '@inertiajs/react';
import { 
    ArrowLeft,
    ChevronDown,
    ChevronUp,
    Clock,
    Eye,
    Filter,
    Plus,
    Search,
    FileText,
    LogOut,
    Building2,
    AlertTriangle,
    CheckCircle,
    XCircle,
    Pause
} from 'lucide-react';

interface ServiceRequest {
    id: number;
    title: string;
    description: string;
    status: string;
    status_label: string;
    priority: string;
    priority_label: string;
    priority_color: string;
    category: string;
    requested_date: string;
    formatted_requested_date: string;
    scheduled_date: string | null;
    formatted_scheduled_date: string | null;
    completed_date: string | null;
    formatted_completed_date: string | null;
    estimated_cost: string | null;
    actual_cost: string | null;
    assigned_to: {
        id: number;
        name: string;
    } | null;
    is_overdue: boolean;
    days_until_scheduled: number | null;
    created_at: string;
    can_cancel: boolean;
}

interface ServiceRequestsProps {
    customer: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        name: string;
    };
    service_requests: ServiceRequest[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        status: string;
        priority: string;
        category: string;
        search: string;
        date_from: string;
        date_to: string;
        sort_by: string;
        sort_order: string;
    };
}

export default function CustomerServiceRequests({ customer, company, service_requests, pagination, filters }: ServiceRequestsProps) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [priority, setPriority] = useState(filters.priority);
    const [category, setCategory] = useState(filters.category);
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);
    const [sortBy, setSortBy] = useState(filters.sort_by);
    const [sortOrder, setSortOrder] = useState(filters.sort_order);

    const handleLogout = () => {
        router.post(route('customer.logout'));
    };

    const applyFilters = useCallback(() => {
        router.get(route('customer.dashboard'), {
            status,
            priority,
            category,
            search,
            date_from: dateFrom,
            date_to: dateTo,
            sort_by: sortBy,
            sort_order: sortOrder,
        }, { preserveState: true });
    }, [status, priority, category, search, dateFrom, dateTo, sortBy, sortOrder]);

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        setPriority('all');
        setCategory('all');
        setDateFrom('');
        setDateTo('');
        setSortBy('created_at');
        setSortOrder('desc');
        router.get(route('customer.dashboard'), {}, { preserveState: true });
    };

    const handleSort = (field: string) => {
        const newOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
        setSortBy(field);
        setSortOrder(newOrder);
        applyFilters();
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'completed':
                return <CheckCircle className="h-4 w-4 text-green-600" />;
            case 'cancelled':
                return <XCircle className="h-4 w-4 text-red-600" />;
            case 'on_hold':
                return <Pause className="h-4 w-4 text-yellow-600" />;
            case 'in_progress':
                return <Clock className="h-4 w-4 text-blue-600" />;
            default:
                return <Clock className="h-4 w-4 text-gray-600" />;
        }
    };

    const getStatusBadge = (request: ServiceRequest) => {
        const variant = request.status === 'completed' ? 'default' :
                      request.status === 'cancelled' ? 'destructive' :
                      request.status === 'in_progress' ? 'secondary' :
                      request.is_overdue ? 'destructive' : 'outline';
        
        return <Badge variant={variant}>{request.status_label}</Badge>;
    };

    const getPriorityBadge = (request: ServiceRequest) => {
        const variant = request.priority === 'urgent' ? 'destructive' :
                      request.priority === 'high' ? 'destructive' :
                      request.priority === 'medium' ? 'secondary' : 'outline';
        
        return <Badge variant={variant}>{request.priority_label}</Badge>;
    };

    const SortIcon = ({ field }: { field: string }) => {
        if (sortBy !== field) return null;
        return sortOrder === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />;
    };

    return (
        <div className="min-h-screen bg-background">
            <Head title="Service Requests" />
            
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
                                <h2 className="text-2xl font-bold tracking-tight">Service Requests</h2>
                                <p className="text-muted-foreground">
                                    View and manage your service requests and their status
                                </p>
                            </div>
                        </div>
                        
                        <Button>
                            <Plus className="h-4 w-4 mr-2" />
                            New Request
                        </Button>
                    </div>

                    {/* Filters */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Filter className="h-5 w-5 mr-2" />
                                Filter Service Requests
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Search</label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search requests..."
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
                                            <SelectItem value="all">All Statuses</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="in_progress">In Progress</SelectItem>
                                            <SelectItem value="completed">Completed</SelectItem>
                                            <SelectItem value="cancelled">Cancelled</SelectItem>
                                            <SelectItem value="on_hold">On Hold</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Priority</label>
                                    <Select value={priority} onValueChange={setPriority}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All priorities" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Priorities</SelectItem>
                                            <SelectItem value="low">Low</SelectItem>
                                            <SelectItem value="medium">Medium</SelectItem>
                                            <SelectItem value="high">High</SelectItem>
                                            <SelectItem value="urgent">Urgent</SelectItem>
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

                    {/* Results */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>
                                    Service Requests ({pagination.total})
                                </CardTitle>
                                <div className="text-sm text-muted-foreground">
                                    Showing {pagination.from}-{pagination.to} of {pagination.total} requests
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {service_requests.length > 0 ? (
                                <div className="space-y-4">
                                    {service_requests.map((request) => (
                                        <div key={request.id} className="border rounded-lg p-6 hover:bg-muted/50">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 space-y-3">
                                                    <div className="flex items-center space-x-4">
                                                        <div className="flex items-center space-x-2">
                                                            {getStatusIcon(request.status)}
                                                            <h3 className="font-semibold text-lg">{request.title}</h3>
                                                        </div>
                                                        
                                                        <div className="flex items-center space-x-2">
                                                            {getStatusBadge(request)}
                                                            {getPriorityBadge(request)}
                                                        </div>
                                                    </div>
                                                    
                                                    <p className="text-muted-foreground">{request.description}</p>
                                                    
                                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                        <div>
                                                            <span className="font-medium">Requested:</span>
                                                            <div className="text-muted-foreground">{request.formatted_requested_date}</div>
                                                        </div>
                                                        
                                                        {request.scheduled_date && (
                                                            <div>
                                                                <span className="font-medium">Scheduled:</span>
                                                                <div className={`text-muted-foreground ${request.is_overdue ? 'text-red-600' : ''}`}>
                                                                    {request.formatted_scheduled_date}
                                                                    {request.is_overdue && (
                                                                        <div className="flex items-center text-red-600 text-xs mt-1">
                                                                            <AlertTriangle className="h-3 w-3 mr-1" />
                                                                            Overdue
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        )}
                                                        
                                                        {request.assigned_to && (
                                                            <div>
                                                                <span className="font-medium">Assigned to:</span>
                                                                <div className="text-muted-foreground">{request.assigned_to.name}</div>
                                                            </div>
                                                        )}
                                                        
                                                        {request.category && (
                                                            <div>
                                                                <span className="font-medium">Category:</span>
                                                                <div className="text-muted-foreground">{request.category}</div>
                                                            </div>
                                                        )}
                                                    </div>
                                                    
                                                    {(request.estimated_cost || request.actual_cost) && (
                                                        <div className="flex items-center space-x-6 text-sm">
                                                            {request.estimated_cost && (
                                                                <div>
                                                                    <span className="font-medium">Estimated Cost:</span>
                                                                    <span className="text-muted-foreground ml-2">${request.estimated_cost}</span>
                                                                </div>
                                                            )}
                                                            {request.actual_cost && (
                                                                <div>
                                                                    <span className="font-medium">Actual Cost:</span>
                                                                    <span className="text-muted-foreground ml-2">${request.actual_cost}</span>
                                                                </div>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                                
                                                <div className="flex items-center space-x-2 ml-4">
                                                    <Link href={`/customer/service-requests/${request.id}`}>
                                                        <Button size="sm" variant="outline">
                                                            <Eye className="h-4 w-4 mr-2" />
                                                            View Details
                                                        </Button>
                                                    </Link>
                                                    
                                                    {request.can_cancel && (
                                                        <Button size="sm" variant="outline">
                                                            Cancel
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <FileText className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <h3 className="mt-2 text-sm font-semibold text-foreground">
                                        No service requests found
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {Object.values(filters).some(v => v && v !== 'all') 
                                            ? 'Try adjusting your filters to see more results.'
                                            : 'Get started by creating your first service request.'
                                        }
                                    </p>
                                    <div className="mt-6">
                                        {Object.values(filters).some(v => v && v !== 'all') ? (
                                            <Button variant="outline" onClick={clearFilters}>
                                                Clear Filters
                                            </Button>
                                        ) : (
                                            <Button>
                                                <Plus className="h-4 w-4 mr-2" />
                                                New Service Request
                                            </Button>
                                        )}
                                    </div>
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