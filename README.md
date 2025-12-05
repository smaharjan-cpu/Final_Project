# Online Computer Store

A comprehensive e-commerce web application for buying computer products and accessories, built with PHP, MySQL, and Bootstrap.

## 📋 Project Description

This is a fully functional online computer store that allows users to browse products, add items to their cart, place orders, and track their purchase history. The application includes a complete admin panel for managing products and orders. It features a modern, responsive design with dark mode support and implements all security best practices.

### Key Features

**User Features:**

- User registration and authentication with secure password hashing
- Browse products by category (Desktop, Laptop, Monitor, Keyboard, Mouse, etc.)
- Advanced search and filter functionality
- Detailed product pages with image galleries and specifications
- Product reviews and ratings system
- Shopping cart with real-time updates
- Wishlist functionality
- Secure checkout process with order confirmation
- Order history tracking with detailed order views
- Responsive design that works on all devices
- Dark mode toggle

**Admin Features:**

- Secure admin dashboard with statistics
- Product management (add, edit, delete products)
- Order management with status updates
- Real-time inventory tracking
- User analytics and revenue reports

**Bonus Features Implemented:**

- ✅ Product search and category filtering
- ✅ Fully responsive admin dashboard
- ✅ Customer reviews and star ratings
- ✅ Automatic inventory updates after orders
- ✅ Session-based cart and authentication

## 🚀 Setup Instructions

### Prerequisites

- **XAMPP** (or any Apache + PHP + MySQL environment)
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, or Edge)

### Installation Steps

1. **Install XAMPP**

   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Clone/Download the Project**

   ```bash
   # Place the project folder in XAMPP's htdocs directory
   C:\xampp\htdocs\Project
   ```

3. **Create the Database**

   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `computer_store`
   - Import the SQL file: `db/database.sql`
   - Or run the automated setup script (see option 4)

4. **Automated Setup (Recommended)**

   - Navigate to: `http://localhost/Project/setup.php`
   - This will automatically:
     - Create all required database tables
     - Seed sample products
     - Create admin and test user accounts
     - Add sample reviews

5. **Configure Database Connection** (if needed)

   - Open `db/db_connect.php`
   - Update database credentials:
     ```php
     $host = 'localhost';
     $db   = 'computer_store';
     $user = 'root';
     $pass = '';
     ```

6. **Access the Application**
   - Homepage: `http://localhost/Project/`
   - Admin Panel: `http://localhost/Project/admin/dashboard.php`

### Default Login Credentials

**Admin Account:**

- Email: `admin@computerstore.com`
- Password: `admin123`

**Test User Account:**

- Email: `john@example.com`
- Password: `user123`

## 📁 Project Structure

```
Project/
├── index.php                 # Homepage
├── products.php              # Product listing with search/filter
├── product.php               # Product detail page
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout page
├── order_history.php         # User order history
├── order_details.php         # Single order details
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # Logout handler
├── header.php                # Common header
├── footer.php                # Common footer
├── setup.php                 # Automated database setup
├── seed_reviews.php          # Add sample reviews
├── includes/                 # Backend API handlers
│   ├── process_order.php     # Order processing
│   ├── submit_review.php     # Review submission handler
│   ├── get_reviews.php       # Fetch reviews API
│   ├── add_to_cart.php       # Add to cart handler
│   ├── remove_from_cart.php  # Remove from cart handler
│   ├── update_cart.php       # Update cart quantity
│   ├── get_cart_count.php    # Get cart count API
│   └── add_to_wishlist.php   # Add to wishlist handler
├── admin/
│   ├── dashboard.php         # Admin dashboard
│   ├── products.php          # Product management
│   └── orders.php            # Order management
├── assets/
│   ├── css/
│   │   ├── style.css         # Custom styles
│   │   └── bootstrap.css     # Bootstrap framework
│   ├── js/
│   │   ├── script.js         # Custom JavaScript
│   │   └── bootstrap.bundle.min.js
│   ├── images/               # Product images
│   └── videos/               # Homepage video
├── db/
│   ├── db_connect.php        # Database connection
│   └── database.sql          # Complete database dump
└── README.md                 # This file
```

## 🛠️ Technologies Used

### Backend

- **PHP 8.x** - Server-side scripting
- **MySQL** - Database management
- **PDO** - Database abstraction with prepared statements

### Frontend

- **HTML5** - Semantic markup
- **CSS3** - Styling with custom properties and animations
- **JavaScript (ES6+)** - Client-side interactivity
- **Bootstrap 5.3** - Responsive framework
- **AJAX** - Asynchronous data loading

### Security Features

- Password hashing with `password_hash()` (BCRYPT)
- Prepared statements to prevent SQL injection
- Session-based authentication
- CSRF protection with form validation
- Input sanitization and validation (client + server side)
- XSS protection with `htmlspecialchars()`

## 💡 Usage Guide

### For Customers

1. **Browse Products**

   - Visit the homepage to see featured products
   - Use the Products page to browse all items
   - Filter by category or use the search function

2. **Add to Cart**

   - Click on a product to view details
   - Select quantity and click "Add to Cart"
   - View cart anytime using the cart icon in the header

3. **Place an Order**

   - Go to cart and click "Proceed to Checkout"
   - Fill in shipping and billing information
   - Submit order and receive confirmation

4. **Write Reviews**

   - Login to your account
   - Visit any product page
   - Scroll to the reviews section and submit your rating

5. **Track Orders**
   - Click on your username in the header
   - Select "Order History"
   - View all orders and their status

### For Administrators

1. **Login as Admin**

   - Use admin credentials to login
   - You'll be redirected to the admin dashboard

2. **Manage Products**

   - Click "Manage Products" to view all products
   - Add new products with images and specifications
   - Edit or delete existing products

3. **Manage Orders**

   - Click "View All Orders" to see customer orders
   - Update order status (Pending → Processing → Shipped → Completed)
   - View detailed order information

4. **Monitor Analytics**
   - Dashboard shows total products, orders, users, and revenue
   - View recent orders and statistics

## 🔧 Database Schema

### Tables

- `users` - User accounts (customers and admins)
- `products` - Product catalog
- `cart` - Shopping cart items
- `wishlist` - User wishlists
- `orders` - Customer orders
- `order_items` - Items in each order
- `reviews` - Product reviews and ratings

## 📝 Features Implementation

### Non-Functional Requirements

✅ **Responsive Design** - Bootstrap grid + custom media queries  
✅ **Data Validation** - Client-side (JavaScript) + Server-side (PHP)  
✅ **Secure Passwords** - BCrypt hashing with `password_hash()`  
✅ **SQL Injection Prevention** - PDO prepared statements

### Bonus Features (All Implemented)

✅ **Product Search & Filter** - Search by name/description + category filters  
✅ **Responsive Admin Dashboard** - Mobile-friendly with statistics  
✅ **Product Reviews/Ratings** - 5-star system with comments  
✅ **Inventory Auto-Update** - Stock decreases when orders placed  
✅ **Session Management** - Cart persistence + authentication

## 🐛 Troubleshooting

**Issue: Database connection failed**

- Ensure MySQL is running in XAMPP
- Verify database credentials in `db/db_connect.php`
- Check that database `computer_store` exists

**Issue: Images not loading**

- Verify images exist in `assets/images/` folder
- Check file paths are correct (relative paths)

**Issue: Can't login as admin**

- Run `setup.php` to create admin account
- Default: admin@computerstore.com / admin123

**Issue: Reviews not showing**

- Run `seed_reviews.php` to add sample reviews
- Or create reviews table by visiting any product page

## 📞 Support

For issues or questions, please refer to the code comments or contact the development team.

## 👨‍💻 Developer Information

**Student Name:** Sumun Maharjan  
**Student ID:** 5143706  
**Course:** Web Development  
**Project:** Online Computer Store - E-commerce Application  
**Submission Date:** December 2025

## 📄 License

This project is created for educational purposes as part of a web development course.

---

**Live Demo:** Not deployed (Local development only)  
**Version:** 1.0.0  
**Last Updated:** December 5, 2025
