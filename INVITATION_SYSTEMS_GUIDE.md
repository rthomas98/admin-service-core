# Invitation Systems Guide

This application has two distinct invitation systems for different purposes:

## 1. Customer Invitations (CustomerInvite)
**Navigation**: Customer Management → Customer Invitations
**Purpose**: Invite customers to access their customer portal
**Role Assigned**: `customer`

### When to Use
- Inviting existing customers to access their portal
- Allowing customers to view invoices
- Enabling customers to submit service requests
- Giving customers ability to manage their account information

### What Customers Can Do
- View and download invoices
- Submit service requests
- View service history
- Update contact information
- Access notifications
- NO admin panel access

### Process Flow
1. Admin sends invitation from Customer Invitations
2. Customer receives email with registration link
3. Customer creates portal account with password
4. Customer logs in at `/customer/login`
5. Customer accesses their portal dashboard

---

## 2. Internal User & Company Owner Invitations (CompanyUserInvite)
**Navigation**: Company Management → Internal User Invites
**Purpose**: Invite internal staff OR company owners who need admin access
**Roles Available**:
- `admin` - Full system administrator
- `company` - Company owner (customer who manages their business)
- `manager` - Operations manager
- `staff` - Limited staff access
- `viewer` - Read-only access

### When to Use
- Adding new staff members to your team
- Inviting a customer's business owner who needs to manage their company profile
- Granting administrative access to the system

### Special Note on "Company" Role
The `company` role is for **customers who own their business** and need to:
- Complete their company onboarding
- Manage their business profile
- Access administrative features for their company
- Set up their organization details

### Process Flow for Company Owners
1. Admin sends invitation with `company` role
2. Business owner receives invitation email
3. Owner accepts invitation and creates account
4. Owner is redirected to `/customer/setup` to complete profile
5. After setup, owner can access admin panel at `/admin`

### Process Flow for Internal Users
1. Admin sends invitation with appropriate role
2. User receives invitation email
3. User accepts invitation and creates account
4. User can immediately access admin panel at `/admin`

---

## Quick Decision Guide

**Question**: Do they need to access the admin panel?
- **No** → Use Customer Invitations (customer portal only)
- **Yes** → Use Internal User Invites

**Question**: Are they a customer's business owner who needs to manage their company?
- **Yes** → Use Internal User Invites with `company` role
- **No** → Use appropriate role based on their responsibilities

**Question**: What level of access should they have?
- View invoices and submit requests → Customer Invitation
- Manage business profile → Internal User Invite (`company` role)
- Full admin access → Internal User Invite (`admin` role)
- Limited staff access → Internal User Invite (`staff` or `viewer` role)

---

## Key Differences Summary

| Feature | Customer Invitations | Internal User Invites |
|---------|---------------------|----------------------|
| Access Level | Customer Portal Only | Admin Panel + Portal |
| Default Role | `customer` | Various (admin, company, staff, etc.) |
| Login URL | `/customer/login` | `/admin/login` |
| Dashboard | Customer Portal Dashboard | Filament Admin Dashboard |
| Can Manage Business | No | Yes (with `company` role) |
| Can View All Customers | No | Yes (based on role permissions) |
| Onboarding Required | No | Yes (for `company` role) |