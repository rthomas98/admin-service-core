import { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, router } from '@inertiajs/react';
import { 
    AlertCircle,
    ArrowLeft,
    ChevronDown,
    ChevronUp,
    Download,
    DollarSign,
    Eye,
    Filter,
    Search,
    Calendar,
    LogOut,
    Building2
} from 'lucide-react';

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    formatted_invoice_date: string;
    due_date: string;
    formatted_due_date: string;
    subtotal: string;
    tax_amount: string;
    total_amount: string;
    amount_paid: string;
    balance_due: string;
    status: string;
    status_label: string;
    is_overdue: boolean;
    is_paid: boolean;
    line_items: any[];
    notes: string;
    service_order: {
        id: number;
        order_number: string;
    } | null;
    payment_count: number;
    last_payment_date: string | null;
}

interface InvoicesProps {
    customer: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        name: string;
    };
    invoices: Invoice[];
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
        search: string;
        date_from: string;
        date_to: string;
        sort_by: string;
        sort_order: string;
    };
}

export default function CustomerInvoices({ customer, company, invoices, pagination, filters }: InvoicesProps) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);
    const [sortBy, setSortBy] = useState(filters.sort_by);
    const [sortOrder, setSortOrder] = useState(filters.sort_order);

    const handleLogout = () => {
        router.post(route('customer.logout'));
    };

    const applyFilters = useCallback(() => {
        router.get(route('customer.invoices'), {
            status,
            search,
            date_from: dateFrom,
            date_to: dateTo,
            sort_by: sortBy,
            sort_order: sortOrder,
        }, { preserveState: true });
    }, [status, search, dateFrom, dateTo, sortBy, sortOrder]);

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        setDateFrom('');
        setDateTo('');
        setSortBy('invoice_date');
        setSortOrder('desc');
        router.get(route('customer.dashboard'), {}, { preserveState: true });
    };

    const handleSort = (field: string) => {
        const newOrder = sortBy === field && sortOrder === 'asc' ? 'desc' : 'asc';
        setSortBy(field);
        setSortOrder(newOrder);
        applyFilters();
    };

    const getStatusBadge = (invoice: Invoice) => {
        if (invoice.is_paid) {
            return <Badge variant="default">Paid</Badge>;
        }
        if (invoice.is_overdue) {
            return <Badge variant="destructive">Overdue</Badge>;
        }
        return <Badge variant="secondary">{invoice.status_label}</Badge>;
    };

    const SortIcon = ({ field }: { field: string }) => {
        if (sortBy !== field) return null;
        return sortOrder === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />;
    };

    return (
        <div className="min-h-screen bg-background">
            <Head title="Invoices" />
            
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
                                <h2 className="text-2xl font-bold tracking-tight">Invoices</h2>
                                <p className="text-muted-foreground">
                                    View and manage your invoices and payment history
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Filters */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center">
                                <Filter className="h-5 w-5 mr-2" />
                                Filter Invoices
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                                <div>
                                    <label className="text-sm font-medium mb-2 block">Search</label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search invoices..."
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
                                            <SelectItem value="paid">Paid</SelectItem>
                                            <SelectItem value="overdue">Overdue</SelectItem>
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
                                    Invoices ({pagination.total})
                                </CardTitle>
                                <div className="text-sm text-muted-foreground">
                                    Showing {pagination.from}-{pagination.to} of {pagination.total} invoices
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {invoices.length > 0 ? (
                                <div className="space-y-4">
                                    {/* Table Header */}
                                    <div className="grid grid-cols-7 gap-4 py-3 px-4 bg-muted rounded-lg text-sm font-medium">
                                        <button 
                                            className="text-left flex items-center space-x-1"
                                            onClick={() => handleSort('invoice_number')}
                                        >
                                            <span>Invoice #</span>
                                            <SortIcon field="invoice_number" />
                                        </button>
                                        
                                        <button 
                                            className="text-left flex items-center space-x-1"
                                            onClick={() => handleSort('invoice_date')}
                                        >
                                            <span>Date</span>
                                            <SortIcon field="invoice_date" />
                                        </button>
                                        
                                        <button 
                                            className="text-left flex items-center space-x-1"
                                            onClick={() => handleSort('due_date')}
                                        >
                                            <span>Due Date</span>
                                            <SortIcon field="due_date" />
                                        </button>
                                        
                                        <button 
                                            className="text-left flex items-center space-x-1"
                                            onClick={() => handleSort('total_amount')}
                                        >
                                            <span>Amount</span>
                                            <SortIcon field="total_amount" />
                                        </button>
                                        
                                        <button 
                                            className="text-left flex items-center space-x-1"
                                            onClick={() => handleSort('balance_due')}
                                        >
                                            <span>Balance</span>
                                            <SortIcon field="balance_due" />
                                        </button>
                                        
                                        <span>Status</span>
                                        <span>Actions</span>
                                    </div>

                                    {/* Table Rows */}
                                    {invoices.map((invoice) => (
                                        <div key={invoice.id} className="grid grid-cols-7 gap-4 py-4 px-4 border rounded-lg hover:bg-muted/50">
                                            <div className="font-medium">
                                                {invoice.invoice_number}
                                            </div>
                                            
                                            <div className="text-sm text-muted-foreground">
                                                {invoice.formatted_invoice_date}
                                            </div>
                                            
                                            <div className="text-sm">
                                                <div className={invoice.is_overdue ? 'text-red-600 font-medium' : ''}>
                                                    {invoice.formatted_due_date}
                                                </div>
                                                {invoice.is_overdue && (
                                                    <div className="flex items-center text-red-600 text-xs">
                                                        <AlertCircle className="h-3 w-3 mr-1" />
                                                        Overdue
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div className="font-medium">
                                                ${invoice.total_amount}
                                            </div>
                                            
                                            <div className={`font-medium ${parseFloat(invoice.balance_due) > 0 ? 'text-orange-600' : 'text-green-600'}`}>
                                                ${invoice.balance_due}
                                            </div>
                                            
                                            <div>
                                                {getStatusBadge(invoice)}
                                            </div>
                                            
                                            <div className="flex items-center space-x-2">
                                                <Link href={`/customer/invoices/${invoice.id}`}>
                                                    <Button size="sm" variant="outline">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => window.location.href = `/api/customer/invoices/${invoice.id}/download`}
                                                >
                                                    <Download className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <DollarSign className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <h3 className="mt-2 text-sm font-semibold text-foreground">
                                        No invoices found
                                    </h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {Object.values(filters).some(v => v && v !== 'all') 
                                            ? 'Try adjusting your filters to see more results.'
                                            : 'Your invoices will appear here once services are provided.'
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
                                                onClick={() => router.get(route('customer.invoices'), {
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
                                                onClick={() => router.get(route('customer.invoices'), {
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