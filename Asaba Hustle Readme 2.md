# Asaba Hustle

Asaba Hustle is a **location-based job marketplace** designed to connect people who need services with skilled workers in their area.

The platform enables users to **post jobs, find service providers, communicate, and complete payments**, all within a simple mobile-first interface.

Typical services include:

- Cleaning
- Plumbing
- Electrical repairs
- Cooking
- Gardening
- Moving assistance
- General handyman services

The system is designed to support **web and future mobile applications** using an **API-first architecture** powered by Laravel.

---

# Project Goals

The goal of Asaba Hustle is to build a scalable platform that:

- Connects local service providers with clients
- Makes it easy to post and find jobs
- Enables real-time communication between users
- Supports secure payments
- Builds trust through ratings and verification

---

# Core Features

## User Authentication

Users can create accounts and log in using their **phone number and password**.

Features include:

- User registration
- Login and logout
- Forgot password
- Phone-based authentication
- Auto login after signup
- Profile management

---

## User Profiles

Each user has a profile containing their personal and professional details.

Profile fields:

- Full name
- Phone number
- Password
- Primary skill
- Profile photo
- Optional ID verification
- Rating and reviews
- Location

Users can update their profile information anytime.

---

## Skills & Categories

Skills represent service categories workers can offer.

Examples include:

- Cleaning
- Plumbing
- Electrical
- Cooking
- Gardening
- Moving help

Each skill will have:

- Name
- Icon
- Description

These skills appear on the home screen as a **categories carousel**.

---

## Job Posting

Users can post jobs describing the service they need.

Job fields include:

- Title
- Description
- Budget
- Location (address or map pin)
- Payment method

Payment options:

- Cash
- Paystack

After submitting the form, the job becomes visible to workers on the platform.

---

## Job Listings

Workers can browse available jobs on the home screen.

Jobs display:

- Job title
- Short description
- Location
- Budget

Users can also search jobs using the **search bar by skill or job title**.

---

## Job Details

The job details screen displays:

- Job description
- Budget
- Location
- Client information

Workers can then:

- Chat with the client
- Call the client
- Accept or request the job

After the job is completed, the client can **mark the job as done and leave a rating**.

---

## Messaging System

The platform will support real-time messaging between clients and workers.

Messaging features:

- Chat between users
- Message history
- Notifications for new messages

The system will use **WebSockets for real-time communication**.

---

## Payments

Payments are supported using **Paystack**.

Payment workflow:

1. Client posts job
2. Client selects payment method
3. Paystack processes the payment
4. Worker completes the job
5. Client confirms completion

Future upgrades may include:

- Escrow system
- Platform commission
- Payment dispute resolution

---

## Ratings and Reviews

After completing a job, users can rate the service provider.

Rating system includes:

- Star rating (1–5)
- Written review
- Average rating displayed on worker profile

This helps maintain trust within the platform.

---

## Notifications

Users receive notifications for important events such as:

- New job postings
- Job applications
- Hire confirmations
- New messages
- Job completion
- Ratings

Notification channels may include:

- In-app notifications
- Email alerts
- SMS alerts (future)

---

# UI Design Guidelines

The platform follows a consistent design system defined in the prototype.

Primary color:

```
#FF7A00
```

Secondary color:

```
#333333
```

Background color:

```
#FFFFFF
```

Typography:

- Bold sans-serif for headings
- Regular sans-serif for body text

Buttons:

- Padding: 12–16px
- Rounded corners

Spacing system:

- 8px grid rhythm
- 12px spacing
- 16px spacing

Icons:

- Simple line icons
- Consistent stroke width

---

# Technology Stack

Backend:

- Laravel (latest version)

Frontend:

- Tailwind CSS
- jQuery

Authentication:

- Laravel Sanctum

Payments:

- Paystack

Database:

- MySQL

Real-time messaging:

- Laravel WebSockets

Storage:

- Laravel filesystem

---

# Architecture

The system follows an **API-first architecture**.

This allows the backend to serve:

- Web applications
- Mobile applications
- Third-party integrations

Architecture overview:

```
Frontend (Web / Mobile)
        |
        v
REST API (Laravel)
        |
        v
Database (MySQL)
```

The web interface consumes the same API used by future mobile applications.

---

# Project Structure

The project follows a modular Laravel structure.

```
app
 ├── Http
 │    ├── Controllers
 │    │      ├── Api
 │    │      └── Web
 │
 ├── Models
 │
 ├── Services
 │
 ├── Repositories
 │
 ├── Events
 │
 └── Notifications
```

Views will be built using:

```
resources/views
```

Frontend assets:

```
resources/js
resources/css
```

---

# Database Structure

Core tables include:

Users
Skills
Jobs
Job Applications
Conversations
Messages
Ratings
Payments
Notifications

---

# API Routes

Authentication

```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/forgot-password
```

Profile

```
GET /api/profile
PUT /api/profile/update
POST /api/profile/upload-id
```

Jobs

```
GET /api/jobs
GET /api/jobs/recent
GET /api/jobs/search
GET /api/jobs/{id}

POST /api/jobs
POST /api/jobs/{id}/apply
POST /api/jobs/{id}/hire
POST /api/jobs/{id}/complete
```

Messaging

```
GET /api/messages
POST /api/messages/send
```

Payments

```
POST /api/payments/initialize
POST /api/payments/verify
```

---

# Development Setup

Clone repository

```
git clone https://github.com/your-org/asaba-hustle.git
cd asaba-hustle
```

Install dependencies

```
composer install
npm install
```

Environment configuration

```
cp .env.example .env
php artisan key:generate
```

Database setup

```
php artisan migrate
```

Start development server

```
php artisan serve
```

Compile frontend assets

```
npm run dev
```

---

# Future Roadmap

The platform is designed to expand with additional features.

Future improvements include:

- Mobile applications (Flutter / React Native)
- Geo-location job matching
- AI-based job recommendations
- Worker verification system
- Escrow payment system
- Platform commission model
- Admin analytics dashboard
- SMS notifications
- Push notifications

---

# Admin Panel

The admin dashboard will allow administrators to manage the platform.

Admin features:

- Manage users
- Manage jobs
- Monitor payments
- Handle disputes
- Manage categories
- Platform analytics

Admin route:

```
/admin
```

---

# Contribution

To contribute:

1. Fork the repository
2. Create a new feature branch
3. Submit a pull request

All code must follow:

- Laravel coding standards
- Clean architecture principles
- RESTful API conventions

---

# License

This project is proprietary software developed for the **Asaba Hustle platform**.

User
├── Skill
├── ServiceJobs (posted)
├── ServiceJobs (assigned)
├── JobApplications
├── RatingsReceived
└── UserNotifications

Skill
└── ServiceJobs

ServiceJob
├── Client
├── Worker
├── Applications
├── Conversation
├── Rating
└── Payment

Conversation
└── Messages
