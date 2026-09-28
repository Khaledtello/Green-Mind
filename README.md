# Green Mind API

A Laravel-based backend for **Green Mind**, an agricultural management platform that combines operational tools for farmers and engineers with AI-powered plant-disease diagnosis and an AI chatbot.

The project focuses on the **backend/API layer**: authentication and authorization, agricultural domain logic, data management, auditability, localization, and integration with a separate Python-based AI service.

> **Note:** The AI models/service themselves are not implemented in this repository. The Laravel backend integrates with the external AI service and manages the application-side workflows and persisted results.

## Overview

Green Mind provides a central API for managing agricultural data and workflows, including:

- Users and role-based access
- Plants and crops
- Plant diseases
- Plant-disease diagnosis history
- Irrigation schedules
- Inventory and inventory usage
- Harvested inventory
- Dashboard data
- Audit logs
- AI-powered plant diagnosis
- AI chatbot

The backend exposes RESTful API endpoints consumed by the client applications and communicates with the external AI service when AI functionality is required.

## Backend Responsibilities

The Laravel application is responsible for:

1. Authenticating users and authorizing their actions.
2. Validating and processing API requests.
3. Managing agricultural data and relationships in MySQL.
4. Applying business rules for plants, crops, inventory, irrigation, and diagnosis workflows.
5. Recording diagnosis history and AI-related application data.
6. Integrating with the external Python AI service.
7. Providing dashboard and audit-log data.
8. Supporting Arabic/English localization at the API layer.
9. Returning structured API responses and documented API endpoints.

## Main Modules

### Authentication & Authorization

The API uses **Laravel Sanctum** for authentication.

Access control is organized around application roles, including:

- Admin
- Engineer
- Farmer

Role-based middleware is used to protect operations that should only be available to specific user types.

### Plant & Crop Management

The backend provides APIs for managing agricultural entities such as plants and crops, including validation, relationships, filtering, pagination, and related business operations.

### Disease Management

Disease records can be managed through the API and are used as part of the plant-diagnosis workflow.

### AI Plant-Disease Diagnosis

The diagnosis flow connects the Laravel API with a separate Python AI service.

At a high level:

```text
Client
  |
  v
Laravel API
  |
  |  image / diagnosis request
  v
Python AI Service
  |
  v
Diagnosis Result
  |
  v
Laravel API
  |
  +--> Persist diagnosis history
  +--> Return result to client
```

The Laravel side handles the application workflow around the AI service, including request validation, image-related data, communication with the external service, and persistence of diagnosis history.

### AI Chatbot

The backend also integrates with the external AI service for chatbot functionality.

Chat requests are validated and logged, while responses can be streamed back to the client using the `text/event-stream` format.

```text
Client
  |
  v
Laravel Chat API
  |
  v
External AI Service
  |
  v
Streaming Response
```

Chat interactions are also persisted through the backend for application-level history and logging.

### Irrigation Scheduling

The application includes APIs and service-layer logic for irrigation schedules, including scheduling and rescheduling operations.

### Inventory Management

The backend provides inventory-related workflows for managing agricultural resources and their usage, including harvested inventory and inventory consumption.

### Dashboard

Dashboard endpoints aggregate application data into statistics and summaries required by the client application.

### Audit Logging

Important user and system actions are recorded through an audit-log module, allowing application activity to be inspected and tracked.

### Localization

The API includes a localization middleware that supports language selection at the request level, including Arabic and English responses.

## Architecture

The project follows Laravel's MVC structure while separating a number of business operations into dedicated services.

A simplified structure is:

```text
app/
├── Enums/
│   └── UserRole.php
│
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
│
├── Models/
│
├── Services/
│   ├── AIService.php
│   ├── DashboardService.php
│   └── ScheduleService.php
│
├── OpenApi/
│
└── Traits/
    └── ApiResponse.php
```

### Request Validation

Dedicated Laravel Form Request classes are used to validate incoming data before controller logic is executed.

Examples include requests for:

- Authentication
- Plant operations
- Crop operations
- Disease operations
- Diagnosis
- Inventory actions
- Scheduling
- Chat
- User management

### API Resources

Laravel API Resources are used to provide structured responses instead of returning raw model data directly from controllers.

### Service Layer

Application logic that goes beyond simple request handling is separated into services, including:

- AI integration
- Dashboard aggregation
- Irrigation scheduling

This keeps controllers focused on handling HTTP concerns while domain/application logic is kept in dedicated classes.

## Database

The project uses **MySQL** as its main relational database.

The database includes application entities such as:

```text
Users
Plants
Crops
Diseases
Diagnosis Histories
Irrigation Schedules
Inventory
Inventory Usage
Harvested Inventory
Chat Logs
Audit Logs
```

The project also includes Laravel migrations for the application's database schema.

## API Design

The API is organized around resource-oriented endpoints and uses standard Laravel request/response mechanisms.

The main API areas include:

```text
/api/auth/...
/api/users/...
/api/plants/...
/api/crops/...
/api/diseases/...
/api/diagnoses/...
/api/schedules/...
/api/inventory/...
/api/chat/...
/api/audit-logs/...
/api/dashboard/...
```

The exact routes are defined in `routes/api.php`.

API documentation/configuration is included through **Scramble/OpenAPI tooling**.

## Technologies

### Backend

- PHP
- Laravel
- RESTful APIs
- Eloquent ORM
- Laravel Sanctum
- Form Requests
- API Resources
- Middleware
- Service Layer

### Database

- MySQL
- Relational data modeling

### Integration & AI

- External Python AI service
- Image-based diagnosis workflow
- AI chatbot integration
- Server-Sent Events (`text/event-stream`) for streamed chat responses

### Authorization & Auditing

- Role-based authorization
- Spatie Laravel Permission
- Activity/audit logging

### Documentation & Tooling

- Scramble / OpenAPI
- Git

## Example AI Diagnosis Flow

A simplified diagnosis request works conceptually like this:

```text
1. Client uploads plant image
2. Laravel validates the request
3. Laravel sends the image/data to the Python AI service
4. AI service returns diagnosis information
5. Laravel stores the diagnosis result/history
6. API returns the structured result to the client
```

The separation allows the Laravel application to remain responsible for application and business logic while the AI workload is handled by a dedicated service.

## Security & Validation

The backend applies several layers of API protection and data validation:

- Token-based authentication with Sanctum
- Role-based authorization middleware
- Dedicated Form Request validation
- Controlled API Resources
- Server-side business-rule validation
- Audit logging for application activity

## Project Structure

```text
.
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── composer.json
└── .env.example
```

## Local Setup

### Requirements

- PHP
- Composer
- MySQL
- A Laravel-compatible local development environment

### Installation

Clone the repository and install dependencies:

```bash
git clone <repository-url>
cd green-mind-api
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Configure the database and external AI service credentials in `.env`.

Run migrations and seed the database:

```bash
php artisan migrate --seed
```

Start the development server:

```bash
php artisan serve
```

> The AI-related endpoints also require the external Python AI service to be available and correctly configured.

## Configuration

The application expects environment-specific values for at least:

- Application configuration
- MySQL connection
- Authentication
- External AI service connection
- Any mail/storage settings required by the enabled application features

Sensitive values must be stored in `.env` and **must never be committed to the repository**.

## What This Project Demonstrates

This project demonstrates practical experience with building a Laravel backend beyond basic CRUD operations, including:

- Designing a multi-module REST API
- Authentication and role-based authorization
- Request validation and structured API responses
- Service-layer application logic
- Relational database design with Eloquent
- External service integration
- AI workflow integration
- Streaming HTTP responses with Server-Sent Events
- Audit logging
- Localization
- Pagination and filtering
- Dashboard data aggregation

## Scope of This Repository

This repository represents the **Laravel/backend side** of Green Mind.

The AI model/service is maintained separately and is consumed by the Laravel API through an integration layer. The focus of this repository is therefore the application's backend architecture, domain logic, persistence, authorization, and communication with external services.

## Author

**Khaled Tello**  
Information Technology Engineering Student — Damascus University  
Backend / Laravel Developer
