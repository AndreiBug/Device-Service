# TechFix - Service Management Platform

TechFix is a web application designed for managing services, devices, and payments in a repair service. The platform enables both employees and clients to view, update, and manage relevant information based on their roles.

---

## Features

### For Clients
- **Invoice Management**: View invoices and their detailed payment breakdowns.
- **Device and Service Overview**: Display associated devices and services sorted by completion date.
- **Top Client Rankings**: Analyze client activity using complex SQL queries.
- **Service and Payment Insights**: Display services and payments based on customizable filters.

### For Employees
- **Service Management**: Add, delete, and update assigned services.
- **Client and Device History**: View comprehensive histories of clients and devices managed by the logged-in employee.
- **Advanced Queries**: Perform complex analyses of clients and services.

---

## Database Structure

### Key Tables
1. **Users**
   - Stores login credentials and role types (`Client` or `Employee`).
   - Relationships: Linked to both `Clients` and `Employees`.

2. **Clients**
   - Contains client-specific details.
   - Relationships: Linked to `Devices` and `Invoices`.

3. **Employees**
   - Stores employee information.
   - Relationships: Linked to `Service_Employees` and indirectly to `Services`.

4. **Devices**
   - Contains details of devices under service.
   - Relationships: Linked to `Clients` and `Services`.

5. **Services**
   - Manages repair and maintenance service details.
   - Relationships: Linked to `Devices` and `Service_Employees`.

6. **Invoices**
   - Handles invoicing for completed services.
   - Relationships: Linked to `Payments` and `Clients`.

7. **Payments**
   - Tracks payment details for invoices.

8. **Service_Employees**
   - Many-to-many relationship table linking services to employees.

---

## Setup Instructions

1. Clone this repository:
   ```bash
   git clone <repository-link>
   ```

2. Import the database:
   - Use the provided SQL file (`techfix_database.sql`) to set up the schema and initial data.

3. Configure database connection:
   - Update the `db_connection.php` file with your database credentials.

4. Run the application:
   - Use a local server (e.g., XAMPP, WAMP) and navigate to `http://localhost/techfix` in your browser.

---

## Technologies Used
- **Front-End**: HTML, CSS, JavaScript
- **Back-End**: PHP
- **Database**: MySQL

