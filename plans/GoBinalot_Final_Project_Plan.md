# GoBinalot --- Final Project Plan

**Project Title:** GoBinalot: A Web-Based Food Ordering, Delivery, and
Transaction Management System for Binalot Sa Bukid

**Document Type:** Pre-development project plan\
**Proposed Stack:** PHP, MySQL, HTML, CSS, JavaScript, Bootstrap 5\
**Project Scope:** Customer ordering, restaurant administration, GCash
payment verification, and delivery management

------------------------------------------------------------------------

## 1. Project Overview

GoBinalot is a proposed web-based system for Binalot Sa Bukid. It will
help organize the restaurant's food ordering, payment verification,
delivery coordination, and transaction recordkeeping in one application.

The system will have three user roles:

1.  **Customer** --- browses the menu, places orders, submits GCash
    payment details, tracks order and delivery progress, views
    transaction history, and submits feedback.
2.  **Admin / Restaurant Staff** --- manages menu items and orders,
    verifies payments, assigns delivery personnel, monitors deliveries,
    reviews feedback, and generates sales reports.
3.  **Delivery Personnel** --- views assigned deliveries, accesses the
    necessary delivery information, and updates delivery progress.

The restaurant will have its own delivery personnel. The initial plan is
to implement status-based delivery tracking rather than live GPS
tracking.

## 2. Project Objectives and Business Context

The system is intended to address difficulties that can arise when a
restaurant relies on phone calls, text messages, and walk-in
transactions to receive and manage orders. These manual processes can
make it harder to organize orders, coordinate deliveries, verify GCash
payments, and maintain accurate transaction and sales records.

GoBinalot will centralize these activities and provide a consistent
record of order, payment, and delivery progress.

## 3. Recommended Technology Stack

  -----------------------------------------------------------------------
  Component               Recommended technology  Purpose
  ----------------------- ----------------------- -----------------------
  Frontend                HTML5, CSS3, JavaScript Structure, styling, and
                                                  browser interactions

  UI framework            Bootstrap 5             Responsive layouts and
                                                  reusable UI components

  Backend                 PHP 8.x, using a        Server-side application
                          supported version       logic

  Database                MySQL 8.x or a          Persistent application
                          maintained compatible   data
                          release                 

  Database access         PDO with prepared       Safer database queries
                          statements              

  Dynamic updates         AJAX with JavaScript or Refresh order and
                          jQuery                  delivery status without
                                                  full page reloads

  Reports/charts          Chart.js (optional)     Visualize sales
                                                  summaries

  Version control         Git and GitHub          Source control and
                                                  collaboration

  Local development       XAMPP or an equivalent  Local testing
                          PHP/MySQL environment   

  Deployment              PHP/MySQL-compatible    Hosting the application
                          hosting with HTTPS      securely
  -----------------------------------------------------------------------

**Implementation note:** Plain PHP with a clear controller/model/view
structure is a reasonable choice if it matches the team's skills and
school requirements. Laravel is an alternative if the team is
comfortable with it. Choose one approach before development rather than
mixing unrelated project structures.

## 4. User Roles and Permissions

### 4.1 Customer

Customers can:

-   Register, log in, log out, and manage their profile.
-   Maintain delivery addresses and contact details.
-   Browse food categories and menu items.
-   Search for food and view descriptions, prices, and availability.
-   Add items to a cart, update quantities, and remove items.
-   Check out and submit an order.
-   View the order reference number and order details.
-   View the restaurant's GCash QR payment instructions.
-   Submit a payment receipt and reference number.
-   View payment verification status.
-   Track order and delivery status.
-   Receive notifications about order and payment status changes.
-   View current and previous transactions.
-   Submit food, delivery, and service ratings and comments after an
    eligible completed order.

Customers must only be able to view and manage their own accounts,
orders, payment submissions, notifications, and feedback.

### 4.2 Admin / Restaurant Staff

Authorized admin users can:

-   View a dashboard with order, payment, delivery, and sales summaries.
-   Create, edit, archive, and manage menu categories and items.
-   Update food prices and availability.
-   View, search, sort, and filter orders.
-   Accept or reject orders, with appropriate reasons where applicable.
-   Update order preparation status.
-   Review GCash payment submissions and receipts.
-   Approve or reject payment submissions and record a reason.
-   Assign or reassign delivery personnel.
-   Monitor delivery progress.
-   View customer information necessary to fulfill orders.
-   Review feedback.
-   Generate daily, weekly, monthly, and custom date-range sales
    reports.
-   Manage authorized staff and delivery accounts, if included in the
    final scope.
-   View relevant activity logs.

Admin access must be enforced on the server, not only by hiding
interface elements.

### 4.3 Delivery Personnel

Delivery personnel can:

-   Log in and log out.
-   View deliveries assigned to them.
-   View the order reference, delivery address, customer contact number,
    and necessary delivery instructions.
-   Update delivery status as the delivery progresses.
-   Record delivery notes or a failed-delivery reason.
-   View their assigned delivery history.

Delivery personnel must not be able to access unassigned deliveries,
payment receipts, all customer transactions, admin settings, or sales
reports unless a specific permission is deliberately granted.

## 5. Functional Requirements

### 5.1 Account and Authentication Management

-   Registration and login for customers.
-   Login for authorized admins and delivery personnel.
-   Secure password hashing and verification.
-   Role-based access control.
-   Profile and delivery-address management.
-   Account activation/deactivation and safe account handling.

### 5.2 Menu Management and Browsing

-   Display categories, item names, descriptions, prices, and
    availability.
-   Allow customers to search and browse the menu.
-   Allow authorized staff to add, edit, and archive menu items.
-   Prevent unavailable or archived items from being ordered.
-   Keep historical order details intact if menu data changes later.

### 5.3 Cart and Checkout

-   Add, update, and remove cart items.
-   Validate quantities and availability on the server.
-   Collect or confirm delivery address and contact details.
-   Display item subtotal, delivery fee, and total amount.
-   Recalculate totals on the server rather than trusting
    browser-submitted values.
-   Create a unique order reference number.
-   Save the order and its line items consistently, using a database
    transaction.

### 5.4 Order Management

-   Allow customers to view their order details and status.
-   Allow admins to review and process orders.
-   Support cancellation or rejection with appropriate rules and
    reasons.
-   Maintain order status history with timestamps and the user
    responsible for each change.
-   Use archival or soft deletion for eligible records instead of
    permanently deleting financial history.
-   Prevent invalid or out-of-sequence status transitions.

### 5.5 GCash QR Payment and Verification

-   Display the restaurant's GCash QR code and payment instructions.
-   Show the amount due for the order.
-   Allow customers to submit a receipt image and GCash reference
    number.
-   Allow authorized admins to inspect the submission and approve or
    reject it.
-   Require a rejection reason where appropriate and allow resubmission
    according to the restaurant's rules.
-   Record the payment amount, submission time, verification status,
    verifier, verification time, and relevant remarks.
-   Keep payment attempts or submissions as separate records where
    needed.

**Important limitation:** A receipt screenshot is not independent proof
that a transfer was successfully received. The authorized restaurant
staff must verify the transfer using the appropriate GCash account or
records. This plan does not assume an automatic GCash payment gateway or
API integration.

### 5.6 Delivery Management

-   Allow admins to assign a delivery person to an eligible order.
-   Show delivery personnel only their assigned deliveries.
-   Allow delivery personnel to update delivery status.
-   Record assignment, pickup, and delivery timestamps.
-   Record failed-delivery reasons and delivery notes.
-   Allow authorized admins to monitor and reassign deliveries.
-   Keep a history of delivery status changes and assignments.

### 5.7 Order and Delivery Tracking

The initial version will use **status-based tracking**. Customers will
see progress updates when the restaurant or delivery person changes a
status. The customer page may use AJAX polling, for example every 5--10
seconds, to refresh the displayed status.

This is not GPS tracking. GPS tracking would require additional
location-sharing functionality, map integration, location permissions,
and further privacy and technical planning.

### 5.8 Notifications

-   Notify customers when an order is accepted or rejected.
-   Notify customers when payment is verified or rejected.
-   Notify customers when the order or delivery status changes.
-   Provide an in-app notification list and read/unread state.
-   Consider email notifications as an optional enhancement.

Notifications should be generated from valid server-side status changes
so that they accurately reflect the stored record.

### 5.9 Transaction History

-   Show customers their own past and current orders.
-   Display order items, price snapshots, total amount, order status,
    and payment status.
-   Provide a detailed view for each order.
-   Preserve historical transactions even when menu items or customer
    addresses change.

### 5.10 Sales Reports

-   Generate daily, weekly, monthly, and custom date-range reports.
-   Display order counts and sales totals.
-   Use clearly defined rules for completed orders, cancellations, and
    refunds.
-   Provide a way to compare report totals with the underlying
    transaction records.
-   Consider CSV export or print-friendly reports as optional
    enhancements.

A recommended starting rule is to count completed, successfully paid
orders as completed sales and exclude cancelled or rejected orders.
Refunds should be recorded and reported separately or deducted according
to the accounting rule agreed with the restaurant.

### 5.11 Customer Feedback

-   Allow eligible customers to rate food quality, delivery, and
    service.
-   Allow optional written comments.
-   Associate feedback with the relevant order and customer.
-   Prevent duplicate feedback for the same order if the business rules
    permit only one review.
-   Allow authorized staff to view and moderate inappropriate feedback
    under documented rules.

### 5.12 Audit and Record Management

-   Record important admin actions, including payment decisions, order
    status changes, delivery assignments, cancellations, and menu
    changes.
-   Preserve payment and transaction history.
-   Use soft deletion or archival for eligible records.
-   Restrict access to audit records to authorized users.

## 6. Order, Payment, and Delivery Statuses

Keep order, payment, and delivery statuses separate because they
represent different processes.

### 6.1 Order Status

Recommended initial values:

-   `pending` --- order has been submitted and awaits review.
-   `confirmed` --- restaurant has accepted the order.
-   `preparing` --- restaurant is preparing the food.
-   `ready` --- food is ready for pickup by delivery personnel.
-   `completed` --- order has been delivered successfully.
-   `cancelled` --- order was cancelled under the defined rules.
-   `rejected` --- restaurant declined the order.

The final status transition rules should be agreed with the restaurant.
For example, the restaurant may require verified payment before
preparation.

### 6.2 Payment Status

Recommended initial values:

-   `unpaid` --- no payment submission has been made.
-   `submitted` --- customer has submitted payment details for review.
-   `verified` --- authorized staff confirmed the payment.
-   `rejected` --- submitted payment details were not accepted.
-   `refunded` --- payment was refunded, if refunds are supported.

If a customer resubmits rejected payment information, preserve the
earlier attempt rather than erasing its history.

### 6.3 Delivery Status

Recommended initial values:

-   `not_assigned` --- no delivery person is assigned.
-   `assigned` --- a delivery person has been assigned.
-   `picked_up` --- delivery person has collected the order.
-   `in_transit` --- delivery is underway.
-   `delivered` --- delivery was completed.
-   `failed` --- delivery could not be completed.

The application should validate allowed transitions and ensure that
delivery personnel can update only deliveries assigned to them.

## 7. Recommended Database Design

The following is a starting schema plan. The exact column definitions,
constraints, indexes, and deletion rules should be finalized in the ERD
and SQL schema before implementation.

  --------------------------------------------------------------------------------
  Table                       Purpose                 Example fields
  --------------------------- ----------------------- ----------------------------
  `users`                     Accounts and roles      `id`, `name`, `email`,
                                                      `password_hash`, `role`,
                                                      `phone`, `is_active`,
                                                      timestamps, optional
                                                      `deleted_at`

  `addresses`                 Saved customer          `id`, `user_id`, `address`,
                              addresses               `contact_number`,
                                                      `is_default`

  `categories`                Menu categories         `id`, `name`, optional
                                                      `deleted_at`

  `menu_items`                Food items and prices   `id`, `category_id`, `name`,
                                                      `description`, `price`,
                                                      `image_path`,
                                                      `is_available`, optional
                                                      `deleted_at`

  `orders`                    Main order record       `id`, `order_number`,
                                                      `user_id`, address snapshot,
                                                      `subtotal`, `delivery_fee`,
                                                      `total_amount`,
                                                      `order_status`, timestamps

  `order_items`               Food items within an    `id`, `order_id`, optional
                              order                   `menu_item_id`, `item_name`,
                                                      `unit_price`, `quantity`,
                                                      `subtotal`

  `payments`                  Payment submissions and `id`, `order_id`, `method`,
                              decisions               `amount`,
                                                      `reference_number`,
                                                      `receipt_path`,
                                                      `payment_status`,
                                                      `verified_by`, timestamps,
                                                      remarks

  `deliveries`                Delivery assignment and `id`, `order_id`,
                              current progress        `delivery_person_id`,
                                                      `delivery_status`,
                                                      assignment/pickup/delivery
                                                      timestamps, failure reason,
                                                      notes

  `order_status_history`      Order status timeline   `id`, `order_id`,
                                                      `old_status`, `new_status`,
                                                      `changed_by`, `remarks`,
                                                      `created_at`

  `delivery_status_history`   Delivery status and     `id`, `delivery_id`, old/new
                              assignment timeline     status, `changed_by`,
                                                      `remarks`, `created_at`

  `notifications`             Customer notifications  `id`, `user_id`, optional
                                                      `order_id`, `title`,
                                                      `message`, `is_read`,
                                                      `created_at`

  `feedback`                  Customer ratings and    `id`, `order_id`, `user_id`,
                              comments                `food_rating`,
                                                      `delivery_rating`,
                                                      `service_rating`, `comment`,
                                                      timestamps

  `activity_logs`             Important actions for   `id`, `user_id`, `action`,
                              accountability          `entity_type`, `entity_id`,
                                                      `created_at`
  --------------------------------------------------------------------------------

### 7.1 Important Database Rules

1.  Use primary keys and foreign keys to enforce relationships.
2.  Use unique constraints for account email and order reference number.
3.  Use a monetary type such as `DECIMAL(10,2)` for prices and totals,
    not floating-point types.
4.  Save an item name and unit-price snapshot in `order_items` so
    historical orders remain accurate after menu changes.
5.  Save the delivery address used for the order as a snapshot so later
    profile changes do not rewrite history.
6.  Store each ordered food item as a separate `order_items` row; do not
    store all items in a comma-separated string.
7.  Keep payment submissions and status history so rejected or
    resubmitted payments remain auditable.
8.  Use database transactions for multi-step operations such as creating
    an order and its items.
9.  Add indexes for commonly searched fields such as order number, user
    ID, statuses, and timestamps.
10. Define foreign-key deletion behavior deliberately. Do not
    cascade-delete completed orders, payment records, or audit history.
11. Use `deleted_at` for eligible soft-deletable records, but prefer
    cancellation or archival for orders and financial records.
12. Decide whether one order may have multiple payment attempts and
    whether an order can have more than one delivery assignment. The
    initial scope can support one active delivery assignment per order
    while preserving reassignment history.

## 8. Proposed Application Structure

For a lightweight plain-PHP application, a possible structure is:

``` text
gobinalot/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Middleware/
├── config/
│   └── database.php
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── routes/
│   └── web.php
├── resources/
│   └── views/
│       ├── customer/
│       ├── admin/
│       ├── delivery/
│       └── layouts/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── storage/
│   └── logs/
├── tests/
├── .env
├── .gitignore
└── README.md
```

This is a suggested organization, not a strict requirement. If Laravel
is selected, use its standard application structure and built-in tools
instead.

Keep database credentials in environment configuration, exclude `.env`
from Git, and configure the web server to expose only the public
directory. Payment receipts should be stored with safe filenames and
access controls; avoid exposing private receipt files through guessable
public URLs.

## 9. Proposed Website Pages

### 9.1 Customer Pages

**Public pages** - Home - Menu and category browsing - Food item
details - About the restaurant - Contact information - Login and
registration

**Authenticated customer pages** - Cart - Checkout - GCash payment
instructions and submission - Order tracking - Order details -
Transaction history - Notifications - Profile and saved addresses -
Feedback form

### 9.2 Admin Pages

-   Admin login
-   Dashboard
-   Menu category management
-   Menu item management
-   Order list and order details
-   Payment verification queue
-   Delivery assignment and monitoring
-   Customer list, if needed
-   Feedback management
-   Sales reports
-   Admin/staff account management, if included
-   Activity logs
-   Restaurant settings, including QR image and delivery fee where
    appropriate

### 9.3 Delivery Personnel Pages

-   Delivery personnel login
-   Assigned deliveries
-   Delivery detail page
-   Delivery status update interface
-   Failed-delivery form
-   Assigned delivery history

All interfaces should be responsive and usable on mobile devices,
especially the customer checkout and delivery personnel pages.

## 10. Security Requirements

-   Hash passwords with PHP `password_hash()` and verify them with
    `password_verify()`.
-   Use PDO prepared statements for database queries.
-   Validate and authorize every protected server-side action.
-   Regenerate session IDs after login and configure secure session
    cookies.
-   Use CSRF protection for state-changing forms and requests.
-   Escape user-generated content before rendering it in HTML.
-   Validate uploaded receipts by size, allowed type, and actual file
    content; generate safe filenames.
-   Restrict receipt access to authorized users and store files outside
    the public web root when possible.
-   Calculate item prices, fees, and totals on the server.
-   Use database transactions for related writes.
-   Validate order, payment, and delivery status transitions.
-   Ensure customers cannot access another customer's records and
    delivery personnel cannot update another person's assigned delivery.
-   Record important administrative actions without logging passwords or
    unnecessary sensitive information.
-   Use HTTPS in the deployed environment and maintain secure backups.
-   Test authorization, input validation, upload handling, and common
    web vulnerabilities before deployment.

## 11. Development Roadmap

### Phase 1 --- Requirements and Design

-   Confirm the restaurant's actual order, payment, and delivery
    processes.
-   Confirm delivery coverage, fees, cancellation rules, and who can
    approve payments.
-   Finalize user roles, statuses, and permissions.
-   Prepare wireframes, use cases, workflow diagrams, and ERD.
-   Write the initial SRS and acceptance criteria.

### Phase 2 --- Project Foundation

-   Choose plain PHP or Laravel.
-   Set up PHP, MySQL, Git, and the local environment.
-   Create the schema and sample data.
-   Implement reusable layouts and navigation.
-   Implement login, sessions, roles, and authorization.

### Phase 3 --- Customer Ordering

-   Build menu browsing and search.
-   Implement cart and checkout.
-   Validate item availability and calculate totals on the server.
-   Save orders and order items.
-   Generate unique order numbers and display order details.

### Phase 4 --- Admin Operations

-   Build the admin dashboard.
-   Implement menu management.
-   Implement order review and status transitions.
-   Add cancellation/rejection rules.
-   Add delivery assignment and monitoring.

### Phase 5 --- Payments and Delivery Personnel

-   Display GCash QR payment instructions.
-   Implement receipt submission and payment verification.
-   Record approval/rejection decisions.
-   Build the delivery personnel dashboard.
-   Implement assignment checks and delivery status history.
-   Add customer tracking updates and notifications.

### Phase 6 --- History, Reports, and Feedback

-   Build customer transaction history.
-   Implement daily, weekly, monthly, and custom-range reports.
-   Validate report calculations.
-   Implement customer ratings and comments.
-   Add optional CSV export or print-friendly reports.

### Phase 7 --- Testing and Deployment

-   Test the complete customer-to-delivery workflow.
-   Test role permissions and record ownership.
-   Test payment resubmission and status transitions.
-   Test calculations, reporting, uploads, and soft deletion.
-   Fix defects and conduct usability testing.
-   Deploy to a PHP/MySQL-compatible server using HTTPS.
-   Prepare user documentation and capstone presentation materials.

## 12. Testing Plan

Prepare test cases with a test ID, preconditions, steps, expected
result, actual result, and pass/fail status.

  -----------------------------------------------------------------------
  Scenario                            Expected result
  ----------------------------------- -----------------------------------
  Register with an existing email     Registration is rejected with a
                                      clear message

  Customer opens an admin page        Access is denied

  Customer requests another           Access is denied
  customer's order                    

  Customer adds several menu items    Quantities and totals are correct

  Browser-submitted total is altered  Server recalculates and rejects
                                      tampering

  Unavailable food is submitted       Order is blocked or requires the
  during checkout                     item to be removed

  Customer submits an invalid receipt Upload is rejected safely
  file                                

  Admin rejects a payment             Status and reason are recorded

  Customer resubmits payment details  Earlier payment attempt remains in
                                      the history

  Admin changes order status          Valid status is saved and visible
                                      to the customer

  Delivery person opens an unassigned Access is denied
  delivery                            

  Delivery person confirms delivery   Only an authorized, assigned
                                      delivery can be completed

  Menu item is archived               Historical order details remain
                                      available

  Sales report is generated           Totals match the defined reporting
                                      rules and eligible transactions

  Customer submits feedback twice     Duplicate feedback is prevented if
                                      only one review per order is
                                      allowed
  -----------------------------------------------------------------------

## 13. Scope and Limitations

### Included in the initial version

-   Customer, admin, and delivery personnel roles.
-   Web-based menu browsing and ordering.
-   GCash QR instructions and manual payment verification.
-   Order management and delivery assignment.
-   Status-based order and delivery tracking.
-   In-app notifications.
-   Transaction history.
-   Sales reports.
-   Customer feedback.
-   Role-based security and basic audit history.

### Optional future enhancements

-   Live GPS tracking and map display.
-   Email or SMS notifications.
-   Automatic GCash payment gateway integration, subject to provider
    availability and approval.
-   Printable receipts and CSV exports.
-   More advanced sales analytics.
-   Inventory forecasting and other restaurant management features.

GPS tracking is excluded from the initial scope unless the owner and
capstone requirements specifically require it. The initial tracking
feature will show status updates, not the delivery person's live
location.

## 14. Requirements to Confirm With the Restaurant Owner

Resolve these business rules before finalizing the database and
implementation:

-   What is the restaurant's exact order acceptance and preparation
    process?
-   Must payment be verified before food preparation begins?
-   Is GCash the only payment method, or will cash or other methods be
    supported?
-   Who verifies that GCash funds were actually received?
-   What is the cancellation policy after order acceptance or payment?
-   How are refunds handled, if applicable?
-   Is the delivery fee fixed or based on delivery location?
-   What delivery areas and operating hours are supported?
-   Can one delivery person handle multiple active orders?
-   What should happen when a delivery fails or a delivery person must
    be reassigned?
-   Is status-based tracking sufficient, or is GPS tracking explicitly
    required?
-   Who can edit menu prices, verify payments, manage users, and view
    reports?
-   What sales figures does the owner need, and how should cancellations
    and refunds affect the reports?
-   How long should order records, payment receipts, and audit logs be
    retained?

Document the answers and use them as the final source of truth for
implementation.

## 15. Pre-Development Checklist

### Requirements

-   [ ] Confirm the restaurant's actual ordering process.
-   [ ] Confirm delivery coverage and delivery fees.
-   [ ] Define order, payment, and delivery statuses.
-   [ ] Confirm status-based tracking versus GPS tracking.
-   [ ] Finalize customer, admin, and delivery permissions.
-   [ ] Agree on cancellation, rejection, and refund rules.

### Design

-   [ ] Create customer and admin wireframes.
-   [ ] Create delivery personnel wireframes.
-   [ ] Create a use-case diagram.
-   [ ] Create order, payment, and delivery flowcharts.
-   [ ] Create the database ERD.
-   [ ] Create the data dictionary.
-   [ ] Define acceptance criteria.

### Technical setup

-   [ ] Choose the final PHP version and architecture.
-   [ ] Set up the local PHP/MySQL environment.
-   [ ] Create the Git repository and `.gitignore`.
-   [ ] Create the database schema and seed data.
-   [ ] Plan authentication, authorization, validation, and file
    security.
-   [ ] Decide where uploaded payment receipts will be stored.

### Capstone documentation

-   [ ] Finalize the project scope and limitations.
-   [ ] Prepare the Software Requirements Specification.
-   [ ] Prepare a development schedule and team assignments.
-   [ ] Prepare test cases and evaluation criteria.
-   [ ] Plan the user manual and final presentation.

------------------------------------------------------------------------

## Final Recommendation

Build GoBinalot in small, testable phases. Start by confirming the
restaurant's real business rules, then finalize the SRS, workflows, ERD,
and SQL schema. Implement authentication and the database foundation
before building the customer ordering flow. Add payment verification and
delivery management after order creation works correctly.

For the initial capstone, prioritize reliable order records, secure
manual GCash verification, clear delivery assignments, accurate reports,
and status-based tracking. These features directly support the stated
business needs while keeping the project achievable with PHP and MySQL.
