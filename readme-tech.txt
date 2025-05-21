# Stratos Pay Technical Stack

## Core Technologies
- WordPress Plugin Architecture
- PHP 7.2+
- WooCommerce Integration
- React 17.0.2
- Node.js
- Vite 4.5.0

## Frontend
- React for dynamic UI components
- WordPress Block Editor integration
- Tailwind CSS for styling
- @wordpress/components: ^19.17.0
- @wordpress/element: ^4.0.0
- @wordpress/i18n: ^4.0.0
- @wordpress/api-fetch: ^6.0.0

## Backend
- WordPress Plugin API
- WooCommerce Payment Gateway API
- Custom database tables for transactions
- PHP OOP architecture
- WP REST API integration

## Payment Processing
- Stratos Pay API integration
- Webhook handling
- Payment intent creation
- Refund processing
- Subscription support

## Security
- WordPress nonce verification
- Input sanitization
- XSS prevention
- CSRF protection
- API key management
- Webhook signature verification

## Performance
- Redis caching (redis: ^4.6.10)
- Rate limiting (rate-limiter-flexible: ^2.4.1)
- Background job processing (bull: ^4.11.3)
- In-memory caching (node-cache: ^5.1.2)

## Monitoring & Logging
- Winston logger (winston: ^3.10.0)
- Sentry error tracking (@sentry/browser: ^7.64.0)
- Custom transaction logging
- Performance monitoring

## Development Tools
- Vite for development server
- WordPress Scripts (@wordpress/scripts: ^24.0.0)
- ESLint for code quality
- PHP CodeSniffer for WordPress standards

## Page Architecture

### Welcome/Dashboard Page
Location: `/admin/pages/welcome.php`
Components:
- Header with logo and description
- Benefits section highlighting key features
- Quick setup section with configuration link
- Call-to-action for new customers
- Support information
Features:
- Responsive design
- Dark mode support
- Interactive UI elements
- Dynamic content loading

### Transactions Page
Location: `/admin/pages/transactions.php`
Components:
- Search and filter form
- Transaction table with pagination
- Status indicators
- Action buttons
Features:
- Real-time updates
- Advanced filtering
- Bulk actions
- Export functionality
- Transaction flagging

### Disputes Page
Location: `/admin/pages/disputes.php`
Components:
- Disputes table with pagination
- Dispute details view
- Resolution workflow
- Communication thread
Features:
- Status tracking
- Document upload
- Response templates
- Timeline view
- Automated notifications

### Settings Page
Location: `/admin/pages/settings.php`
Components:
- API configuration form
- Environment selector
- Webhook configuration
- Support section
Features:
- Form validation
- Secure key storage
- Test mode toggle
- Connection testing
- Auto-save functionality

## Database Schema

### Transactions Table
```sql
CREATE TABLE {prefix}stratos_pay_transactions (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    transaction_id varchar(100) NOT NULL,
    amount decimal(10,2) NOT NULL,
    currency varchar(3) NOT NULL,
    status varchar(20) NOT NULL,
    customer_email varchar(100) NOT NULL,
    customer_name varchar(100) NOT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_disputed tinyint(1) NOT NULL DEFAULT 0,
    dispute_reason varchar(255) DEFAULT NULL,
    dispute_status varchar(20) DEFAULT NULL,
    dispute_date datetime DEFAULT NULL,
    is_flagged tinyint(1) NOT NULL DEFAULT 0,
    metadata text,
    PRIMARY KEY (id),
    KEY transaction_id (transaction_id),
    KEY status (status),
    KEY created_at (created_at),
    KEY is_disputed (is_disputed),
    KEY is_flagged (is_flagged)
)
```

### Order Meta Table
```sql
CREATE TABLE {prefix}stratos_pay_order_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    order_id bigint(20) NOT NULL,
    meta_key varchar(255) NOT NULL,
    meta_value longtext,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY meta_key (meta_key),
    KEY created_at (created_at)
)
```

## Features
- Payment processing
- Transaction management
- Dispute handling
- Fraud detection
- Subscription management
- Refund processing
- Webhook handling
- Express checkout
- Admin dashboard
- Transaction reporting
- Dark mode support

## Installation Requirements
- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.2+
- MySQL 5.6+
- SSL certificate
- Stratos Pay merchant account

## Development Setup
1. Install WordPress and WooCommerce
2. Clone repository
3. Run `npm install`
4. Configure environment variables
5. Run `npm run dev` for development
6. Run `npm run build` for production

## Deployment
- Ensure all WordPress requirements are met
- Verify WooCommerce compatibility
- Check PHP version compatibility
- Configure API keys
- Set up webhooks
- Test payment processing
- Monitor error logs

## Testing
- Unit tests for PHP classes
- Integration tests for WooCommerce
- End-to-end payment testing
- Security testing
- Performance testing
- Cross-browser testing

## Security Considerations
- API key storage
- Payment data handling
- PCI compliance
- XSS prevention
- CSRF protection
- Input validation
- Output sanitization
- Error handling
- Access control
- Rate limiting

## Page Content Structure

### Welcome Page Content
- Hero section with plugin description
- Feature highlights with icons
- Quick setup guide
- Support resources
- Call-to-action buttons
- Latest updates section

### Transactions Page Content
- Search bar with advanced filters
- Transaction list with columns:
  - Transaction ID
  - Amount
  - Customer details
  - Status
  - Date
  - Actions
- Pagination controls
- Bulk action tools
- Export options

### Disputes Page Content
- Active disputes overview
- Dispute list with columns:
  - Transaction ID
  - Amount
  - Customer
  - Dispute reason
  - Status
  - Date
  - Actions
- Resolution workflow steps
- Communication tools
- Document management

### Settings Page Content
- API credentials section
  - Test mode toggle
  - API key inputs
  - Environment selector
- Webhook configuration
  - Webhook URL display
  - Secret key management
- Support section
  - Documentation links
  - Contact information
  - Troubleshooting guide