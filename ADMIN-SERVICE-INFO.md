# Admin Service Core - Unified Management Platform

## 🎛️ Platform Overview

**Admin Service Core** is the centralized administrative dashboard that provides unified management and oversight for LIV Transport LLC and RAW Disposal LLC operations. This internal platform enables seamless coordination between transportation logistics and waste management services, offering real-time insights, customer management, and operational control from a single interface.

Built on modern web technologies, the platform ensures secure, efficient, and scalable management of multi-service operations across the Gulf Coast region, supporting both companies' commitment to excellence in service delivery.

## 🔗 Managed Services Integration

### LIV Transport LLC
- Fleet management and dispatch coordination
- Equipment tracking and availability
- Route optimization and scheduling
- Driver assignments and certifications
- DBE compliance documentation
- Transportation metrics and analytics

### RAW Disposal LLC
- Container inventory management
- Service scheduling and routing
- Equipment maintenance tracking
- Customer account management
- Waste disposal compliance
- Service area coverage management

## 💼 Core Administrative Functions

### 1. Unified Dashboard
- **Real-time Operations Overview** - Combined view of both services
- **Key Performance Indicators** - Revenue, utilization, service metrics
- **Alert Management** - Critical issues, maintenance due, compliance deadlines
- **Quick Actions** - Common tasks accessible from dashboard

### 2. Customer Management
- **Unified Customer Database** - Single view of customers using either/both services
- **Account Management** - Billing, contracts, service history
- **Communication Hub** - Email, SMS, and notification management
- **Customer Portal Access** - Manage customer self-service features

### 3. Operations Control
- **Service Scheduling** - Integrated calendar for both companies
- **Resource Allocation** - Optimize equipment and personnel deployment
- **Route Planning** - Coordinate deliveries and pickups efficiently
- **Emergency Response** - Rapid deployment for urgent requests

### 4. Financial Management
- **Integrated Billing** - Combined or separate invoicing options
- **Revenue Tracking** - Service-specific and consolidated reporting
- **Payment Processing** - Multiple payment methods and terms
- **Financial Analytics** - Profitability analysis by service line

### 5. Compliance & Documentation
- **Regulatory Compliance** - Track permits, licenses, certifications
- **Document Management** - Centralized storage for all business documents
- **Audit Trails** - Complete activity logging for accountability
- **Report Generation** - Automated compliance and operational reports

## 🛡️ Security & Access Control

### Role-Based Access Levels
- **Super Admin** - Full system access, configuration control
- **Operations Manager** - Service management, reporting, customer access
- **Dispatcher** - Scheduling, routing, driver communication
- **Customer Service** - Customer accounts, basic scheduling
- **Finance** - Billing, payments, financial reports
- **View Only** - Read access for stakeholders

### Security Features
- Multi-factor authentication (MFA)
- Session management and timeout controls
- IP whitelisting for sensitive operations
- Encrypted data transmission and storage
- Regular security audits and updates

## 📊 Reporting & Analytics

### Operational Reports
- Service utilization rates
- Equipment availability and maintenance
- Route efficiency metrics
- Driver performance tracking
- Customer satisfaction scores

### Financial Reports
- Revenue by service type
- Profitability analysis
- Accounts receivable aging
- Cost center analysis
- Budget vs. actual comparisons

### Compliance Reports
- Certification status tracking
- Safety incident reporting
- Environmental compliance
- DBE participation metrics
- Insurance and permit renewals

## 🔄 Integration Capabilities

### Current Integrations
- **Database:** Shared PostgreSQL for all services
- **Authentication:** Unified login across platforms
- **Session Management:** Single sign-on capability

### Planned Integrations
- **GPS Tracking:** Real-time fleet location
- **Accounting Software:** QuickBooks/SAP integration
- **Customer Portal:** Self-service capabilities
- **Mobile Apps:** Field service applications
- **Payment Gateways:** Multiple processor support

## 🎨 Brand Guidelines (Unified)

### Brand Colors
```css
/* Primary Brand Colors - Shared Across Services */
--primary-purple: #5C2C86;        /* Deep Purple - Primary brand color */
--primary-purple-light: #A06CD5;  /* Light Purple - Secondary/accent */
--primary-purple-lighter: #E2CFEA;/* Lightest Purple - Backgrounds/tints */

/* Dark Colors */
--primary-dark: #102B3F;          /* Dark Blue - Headers/important text */
--primary-darker: #011A21;        /* Darkest Blue - High contrast elements */

/* Neutral */
--white: #FFFFFF;                 /* Pure white - Backgrounds/text on dark */

/* Extended Palette for Admin UI */
--text-primary: #011A21;          /* Primary text color */
--text-secondary: #102B3F;        /* Secondary text color */
--text-light: #5C2C86;            /* Light text/links */
--bg-light: #FFFFFF;              /* Light backgrounds */
--bg-accent: #E2CFEA;             /* Accent backgrounds */
--border-light: #E2CFEA;          /* Light borders */
--border-default: #A06CD5;        /* Default borders */

/* Interactive States */
--hover-purple: #A06CD5;          /* Hover state for purple elements */
--active-purple: #5C2C86;         /* Active/pressed state */
--focus-ring: #A06CD5;            /* Focus ring color */

/* Status Colors */
--success: #10B981;               /* Green - Success states */
--warning: #F59E0B;               /* Amber - Warning states */
--error: #EF4444;                 /* Red - Error states */
--info: #3B82F6;                  /* Blue - Info states */

/* Service-Specific Accents */
--liv-accent: #5C2C86;            /* LIV Transport purple */
--raw-accent: #2563EB;            /* RAW Disposal blue */
```

### Typography
```css
/* Font Families */
--font-heading: 'Inter', system-ui, -apple-system, sans-serif;
--font-body: 'Inter', system-ui, -apple-system, sans-serif;
--font-mono: 'JetBrains Mono', 'Courier New', monospace; /* For data/codes */

/* Font Weights */
--font-light: 300;
--font-regular: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;
--font-extrabold: 800;

/* Admin-Specific Font Sizes */
--text-xs: 0.75rem;     /* 12px - Labels, captions */
--text-sm: 0.875rem;    /* 14px - Secondary text */
--text-base: 1rem;      /* 16px - Body text */
--text-lg: 1.125rem;    /* 18px - Emphasized text */
--text-xl: 1.25rem;     /* 20px - Section headers */
--text-2xl: 1.5rem;     /* 24px - Page headers */
--text-3xl: 1.875rem;   /* 30px - Dashboard titles */
```

### UI Components (Admin-Specific)
- **Navigation:** Dark sidebar (#011A21) with purple accent for active items
- **Data Tables:** White background, alternating row colors (#F9FAFB), purple sorting indicators
- **Charts:** Purple primary (#5C2C86), blue secondary (#2563EB), extended palette for data visualization
- **Forms:** White backgrounds, purple focus states, inline validation messages
- **Modals:** White content, dark overlay (rgba(1, 26, 33, 0.5)), purple action buttons
- **Alerts:** Status-specific colors with icons, dismissible with animation
- **Badges:** Service-specific colors (LIV purple, RAW blue) for quick identification

## 🚀 Technical Architecture

### Technology Stack
- **Backend:** Laravel 12 with PHP 8.3
- **Frontend:** React 19 with TypeScript
- **State Management:** InertiaJS v2 with SSR
- **Styling:** Tailwind CSS 4.0
- **Database:** PostgreSQL (shared: serve_core_db)
- **Testing:** Pest v4 with browser testing
- **Routing:** Laravel Wayfinder for advanced routing

### Key Technical Features
- Server-side rendering for optimal performance
- TypeScript action system mirroring PHP controllers
- Real-time data synchronization
- Responsive design for tablet and desktop
- Progressive web app capabilities
- Offline mode for critical functions

### Database Schema Extensions (Planned)
```sql
-- Service Management
- services (id, name, type, status)
- service_requests (id, service_id, customer_id, details)
- equipment (id, service_id, type, status, location)

-- Customer Management  
- customers (id, name, email, phone, company)
- customer_services (customer_id, service_id, status)
- contracts (id, customer_id, terms, expiration)

-- Operations
- schedules (id, service_id, date, assignments)
- routes (id, service_id, stops, optimization)
- dispatches (id, route_id, driver_id, status)

-- Financial
- invoices (id, customer_id, amount, status)
- payments (id, invoice_id, method, amount)
- pricing_rules (id, service_id, criteria, rates)

-- Compliance
- certifications (id, type, expiration, documents)
- permits (id, type, jurisdiction, expiration)
- incidents (id, type, date, resolution)
```

## 🔧 Development Roadmap

### Phase 1: Foundation (Current)
- ✅ Basic authentication and authorization
- ✅ Dashboard framework
- ✅ Database connectivity
- ⏳ Role-based access control
- ⏳ Basic reporting structure

### Phase 2: Core Features
- [ ] Customer management module
- [ ] Service scheduling interface
- [ ] Basic billing integration
- [ ] Operational dashboards
- [ ] Email notification system

### Phase 3: Advanced Features
- [ ] Route optimization algorithms
- [ ] Predictive maintenance scheduling
- [ ] Advanced analytics and BI
- [ ] Mobile companion apps
- [ ] Customer self-service portal

### Phase 4: Integration & Automation
- [ ] Third-party API integrations
- [ ] Automated dispatch system
- [ ] AI-powered insights
- [ ] Advanced compliance automation
- [ ] Multi-company expansion capability

## 📱 Access Information

### Development Environment
- **URL:** https://admin-service-core.test
- **Database:** serve_core_db (PostgreSQL)
- **Port:** 5432

### User Roles Configuration
```php
// Example role definitions
'roles' => [
    'super_admin' => ['*'],
    'operations_manager' => ['dashboard', 'customers', 'services', 'reports'],
    'dispatcher' => ['dashboard', 'scheduling', 'routes', 'drivers'],
    'customer_service' => ['customers', 'service_requests'],
    'finance' => ['billing', 'payments', 'financial_reports'],
    'viewer' => ['dashboard:view', 'reports:view']
]
```

## 🔐 Security Protocols

### Authentication Flow
1. Multi-factor authentication required for admin roles
2. Session timeout after 30 minutes of inactivity
3. Concurrent session limiting
4. Password complexity requirements
5. Regular password rotation for sensitive roles

### Data Protection
- Encryption at rest for sensitive data
- TLS/SSL for all connections
- Regular security audits
- GDPR/CCPA compliance measures
- Automated backups with encryption

## 📈 Performance Metrics

### Target KPIs
- Page load time: < 2 seconds
- API response time: < 200ms
- Database query time: < 100ms
- Uptime: 99.9% availability
- Concurrent users: 100+ supported

### Monitoring
- Real-time performance dashboards
- Error tracking and alerting
- User activity analytics
- System health monitoring
- Automated performance reports

## 🤝 Support & Maintenance

### Internal Support Structure
- **Technical Admin:** System configuration and troubleshooting
- **Operations Support:** User training and process optimization
- **Development Team:** Feature development and bug fixes
- **Security Team:** Monitoring and incident response

### Documentation
- User guides for each role
- API documentation for integrations
- System administration manual
- Troubleshooting knowledge base
- Video training materials

---

*Last Updated: January 2025*
*Version: 1.0.0*
*Status: Development Phase*