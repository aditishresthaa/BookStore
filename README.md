A modern e-commerce platform for books featuring an intelligent recommendation engine. Built with PHP and MySQL, it includes secure Khalti payment integration, email verification, and a comprehensive admin dashboard for inventory management.

Key Features:
Smart Recommendation Engine: Uses Content-Based Filtering and the Cosine Similarity algorithm to suggest books based on their title, category, and metadata.
Integrated Digital Payments: Full support for the Khalti Payment Gateway to facilitate secure and seamless online transactions.
Email Verification: Ensures a secure user base by integrating PHPMailer for automated registration confirmation links.
Dynamic Admin Dashboard: A robust control panel for administrators to manage the book catalog (CRUD), monitor customer orders, and handle user data.
Advanced Search & Filter: Intuitive tools for users to find specific titles quickly or filter by genres such as Fiction, Romance, Thriller, and Poetry.
Order Tracking: Real-time status updates (Pending/Completed) and a detailed purchase history for every registered user.
Responsive Design: A clean and modern user interface styled with a professional purple and white theme.

Frontend: HTML5, CSS3 (Purple & White Theme), JavaScript
Backend: PHP (Core PHP)
Database: MySQL
Server Environment: XAMPP (Apache)
API Integrations: Khalti SDK, PHPMailer (SMTP)

How the Recommendation Works
The system analyzes book metadata to create Descriptive Vectors. It then calculates the mathematical angle between these vectors using Cosine Similarity to produce a match score between 0 and 1. Books with the highest scores are automatically ranked and displayed in the "Recommended for You" section.
