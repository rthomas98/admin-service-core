# Admin Service Core - Design Principles & Standards

## I. Core Design Philosophy

*   [ ] **Fleet-First Focus:** Prioritize fleet management workflows, vehicle tracking, and operational efficiency in every design decision.
*   [ ] **Operational Excellence:** Design for logistics professionals who need quick access to critical information and actions.
*   [ ] **Real-Time Responsiveness:** Ensure immediate feedback for time-sensitive operations (emergency services, driver assignments, route planning).
*   [ ] **Data Clarity:** Complex operational data must be presented clearly and actionably.
*   [ ] **Multi-Company Flexibility:** Support both LIV Transport and RAW Disposal workflows seamlessly.
*   [ ] **Consistency Across Services:** Maintain uniform design language across all modules (fleet, waste, financial, customer).
*   [ ] **Accessibility (WCAG AA+):** Ensure all interfaces work for drivers, dispatchers, and administrators with varying abilities.
*   [ ] **Mobile-Ready Operations:** Critical functions must work on tablets and phones for field operations.

## II. Design System Foundation

### Color Palette
*   [ ] **Primary Brand Colors:**
    *   [ ] LIV Transport Blue: For transport-related modules
    *   [ ] RAW Disposal Green: For waste management modules
    *   [ ] Admin Purple: For administrative functions
*   [ ] **Neutrals:** Tailwind gray scale (50-950) for text, backgrounds, borders
*   [ ] **Semantic Colors:**
    *   [ ] Success (green-600): Completed deliveries, active vehicles
    *   [ ] Error/Critical (red-600): Emergency services, overdue maintenance
    *   [ ] Warning (amber-600): Upcoming maintenance, expiring documents
    *   [ ] Informational (blue-600): General notifications, status updates
*   [ ] **Dark Mode:** Full dark mode support for night shift operations

### Typography (Using Tailwind v4)
*   [ ] **Font Family:** System UI stack for performance
*   [ ] **Scale:**
    *   [ ] H1: text-3xl (32px) - Page titles
    *   [ ] H2: text-2xl (24px) - Section headers
    *   [ ] H3: text-xl (20px) - Subsection headers
    *   [ ] Body: text-base (16px) - Default text
    *   [ ] Small: text-sm (14px) - Secondary info
    *   [ ] Caption: text-xs (12px) - Timestamps, metadata
*   [ ] **Weights:** font-normal (400), font-medium (500), font-semibold (600), font-bold (700)

### Spacing System
*   [ ] **Base Unit:** 8px (Tailwind's default)
*   [ ] **Scale:** space-1 (4px) through space-12 (48px)
*   [ ] **Consistent Gap Usage:** Use gap utilities for lists and grids

### Border & Radius
*   [ ] **Border Radius:**
    *   [ ] Small: rounded (4px) - Inputs, buttons
    *   [ ] Medium: rounded-lg (8px) - Cards, modals
    *   [ ] Large: rounded-xl (12px) - Feature cards
*   [ ] **Border Colors:** Use border-gray-200 (light) / border-gray-700 (dark)

## III. Filament-Specific Components

### Resource Tables
*   [ ] **Column Organization:**
    *   [ ] ID/Reference first
    *   [ ] Primary identifier (name/title) second
    *   [ ] Status indicators with color coding
    *   [ ] Actions last
*   [ ] **Row Density:** Comfortable spacing for touch targets
*   [ ] **Bulk Actions:** Clear selection states and action toolbar
*   [ ] **Search & Filters:** Prominent placement above table

### Forms & Schemas
*   [ ] **Section Organization:** Group related fields logically
*   [ ] **Field Labels:** Clear, consistent terminology
*   [ ] **Helper Text:** Provide context for complex fields
*   [ ] **Validation:** Inline error messages with clear instructions
*   [ ] **Required Indicators:** Consistent asterisk placement

### Dashboard Widgets
*   [ ] **Stats Cards:** Large numbers with clear labels and trend indicators
*   [ ] **Charts:** Consistent color coding across all visualizations
*   [ ] **Alert Widgets:** High contrast for critical information
*   [ ] **Table Widgets:** Compact but readable for dashboard context

## IV. Module-Specific Design Guidelines

### Fleet Management
*   [ ] **Vehicle Status Indicators:**
    *   [ ] Green: Active/Available
    *   [ ] Yellow: In Transit/Assigned
    *   [ ] Orange: Maintenance Due
    *   [ ] Red: Out of Service
*   [ ] **Driver Assignment Cards:** Photo, name, vehicle, current status
*   [ ] **Maintenance Timeline:** Visual calendar/timeline for scheduled maintenance
*   [ ] **Fuel Efficiency Charts:** Clear MPG/consumption visualizations

### Waste Management
*   [ ] **Route Visualization:** Clear route cards with stop counts and completion status
*   [ ] **Collection Schedule:** Calendar view with color-coded routes
*   [ ] **Disposal Site Cards:** Capacity indicators, location, accepted waste types
*   [ ] **Volume Tracking:** Bar/line charts for daily/weekly/monthly volumes

### Financial Management
*   [ ] **Invoice Status:**
    *   [ ] Green: Paid
    *   [ ] Yellow: Pending
    *   [ ] Orange: Overdue <30 days
    *   [ ] Red: Overdue >30 days
*   [ ] **Payment History:** Timeline view with amounts and methods
*   [ ] **Revenue Charts:** Clear KPI cards with period comparisons

### Emergency Services
*   [ ] **High Visibility Alerts:** Red banner/card for active emergencies
*   [ ] **Quick Action Buttons:** Large, clearly labeled emergency actions
*   [ ] **Status Timeline:** Real-time updates on emergency response

### Customer Management
*   [ ] **Customer Type Badges:** Visual distinction between customer types
*   [ ] **Service History:** Chronological list with status indicators
*   [ ] **Contact Information:** Clearly organized with click-to-call/email

## V. Interaction Patterns

### Navigation
*   [ ] **Persistent Sidebar:** Collapsible with icons and labels
*   [ ] **Module Grouping:** 
    *   [ ] Fleet Operations
    *   [ ] Waste Management
    *   [ ] Financial
    *   [ ] Customer Service
    *   [ ] Administration
*   [ ] **Breadcrumbs:** Clear path indication for deep navigation

### Actions & Feedback
*   [ ] **Primary Actions:** Prominent placement, primary color
*   [ ] **Destructive Actions:** Require confirmation, red styling
*   [ ] **Loading States:** Skeleton screens for data, spinners for actions
*   [ ] **Success Messages:** Green toast notifications
*   [ ] **Error Handling:** Clear error messages with recovery actions

### Data Entry
*   [ ] **Smart Defaults:** Pre-fill common values
*   [ ] **Auto-Complete:** For customer, vehicle, driver searches
*   [ ] **Date/Time Pickers:** Consistent format, timezone awareness
*   [ ] **Bulk Operations:** Multi-select with clear action toolbar

## VI. Responsive Design Requirements

### Desktop (1440px+)
*   [ ] Full sidebar navigation
*   [ ] Multi-column layouts for dashboards
*   [ ] Side-by-side form sections
*   [ ] Full table columns visible

### Tablet (768px - 1439px)
*   [ ] Collapsible sidebar
*   [ ] 2-column grid maximum
*   [ ] Stacked form sections
*   [ ] Horizontal scroll for wide tables

### Mobile (< 768px)
*   [ ] Bottom navigation for key actions
*   [ ] Single column layouts
*   [ ] Accordion-style form sections
*   [ ] Card-based list views instead of tables

## VII. Performance & Technical Standards

### React/Inertia Optimization
*   [ ] Lazy load heavy components
*   [ ] Implement virtual scrolling for long lists
*   [ ] Use React.memo for expensive renders
*   [ ] Optimize bundle size with code splitting

### Filament Integration
*   [ ] Leverage Filament's built-in components
*   [ ] Use Filament's notification system
*   [ ] Implement proper resource relationships
*   [ ] Utilize Filament's permission system

### Tailwind CSS v4
*   [ ] Use new v4 syntax (@import "tailwindcss")
*   [ ] Leverage CSS custom properties for theming
*   [ ] Implement proper dark mode with dark: variants
*   [ ] Use new color opacity syntax (bg-black/50)

## VIII. Accessibility Checklist

*   [ ] **Keyboard Navigation:** All interactive elements accessible via keyboard
*   [ ] **Focus States:** Clear visual indicators for focused elements
*   [ ] **ARIA Labels:** Proper labels for screen readers
*   [ ] **Color Contrast:** Minimum 4.5:1 for normal text, 3:1 for large text
*   [ ] **Error Messages:** Associated with form fields
*   [ ] **Skip Links:** For main content areas
*   [ ] **Alt Text:** For all informational images
*   [ ] **Table Headers:** Proper th elements with scope

## IX. Testing & Quality Assurance

### Visual Testing
*   [ ] Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
*   [ ] Responsive breakpoint testing
*   [ ] Dark/light mode consistency
*   [ ] Print stylesheet for reports

### Functional Testing
*   [ ] Form validation and submission
*   [ ] Data table sorting and filtering
*   [ ] Chart interactivity
*   [ ] Real-time updates (notifications, status changes)

### Performance Testing
*   [ ] Page load time < 3 seconds
*   [ ] Time to interactive < 5 seconds
*   [ ] Smooth scrolling and animations (60fps)
*   [ ] Efficient API calls and data loading

## X. Documentation & Maintenance

*   [ ] **Component Library:** Document all custom components
*   [ ] **Design Tokens:** Maintain in Tailwind config
*   [ ] **Pattern Library:** Document common UI patterns
*   [ ] **Change Log:** Track design system updates
*   [ ] **Onboarding Guide:** For new developers/designers

---

**Note:** This design system aligns with Laravel 12, Filament v4, React 19, and Tailwind CSS v4 as specified in the project configuration. All implementations should follow Laravel Boost guidelines and existing project conventions.