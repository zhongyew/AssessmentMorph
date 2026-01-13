# ✍️ Grammar Checker & Token Billing System

A professional full-stack web application built with **Laravel 11** and **Vue 3 (Vite)** that provides real-time English grammar analysis, dynamic token-based billing, and persistent history tracking.

## 🌟 Key Features

* **Intelligent Analysis**: Leverages the LanguageTool API to identify spelling and grammatical errors with high precision.
* **Live Highlight & Diff**: Visualizes errors directly in the original text using custom CSS highlighting and provides a "Corrected" view for easy comparison.
* **Dynamic Token Billing**: Implements a real-time cost calculation system where 100 characters = 1 Token.
* **Currency & History Tracking**: Automatically saves correction records to a MySQL database, including used tokens, simulated USD fees ($0.99/token), and timestamps.
* **Input Guarding**: Robust Regex-based language detection ensures only English characters are processed, preventing invalid API calls and token waste.
* **Interactive UI**: Features a modern "Glassmorphism" design with a custom top-up modal and responsive project cards.

## 🛠️ Tech Stack

* **Frontend**: Vue 3 (Composition API), Vite, Axios, CSS3 Animations.
* **Backend**: PHP 8.2.12, Laravel 11.
* **Database**: MySQL (XAMPP).
* **API**: LanguageTool V2 (Public API).



## 📦 Installation & Setup

### 1. Prerequisites
* **Node.js**: v24.12.0 (Tested)
* **PHP**: 8.2.12
* **Database**: XAMPP (Apache & MySQL)

### 2. Backend Configuration
```bash
# Navigate to backend folder
cd backend

# Install PHP dependencies
composer install

# Database Setup:
# 1. Create a database named 'grammar_checker' in phpMyAdmin
# 2. Run migrations to create tables
php artisan migrate

# Start Laravel server
php artisan serve
3. Frontend Configuration
Bash

# Navigate to frontend folder
cd frontend

# Install JS dependencies
npm install

# Start Vite development server
npm run dev
📂 Project structure (Core)
frontend/src/components/HelloWorld.vue: Contains the core logic for Regex validation, Token calculation, and result highlighting.

backend/app/Http/Controllers/GrammarController.php: Manages API requests and database CRUD operations for history records.

backend/routes/api.php: Defines API endpoints for checking text and managing history.

📝 Usage Notes
Token System: Users start with a default balance. If tokens are insufficient, the "Correction" button will disable and prompt a top-up.

Validation: The system automatically detects non-English inputs (e.g., Chinese characters) and alerts the user immediately.