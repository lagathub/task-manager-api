# Task Manager API

A RESTful Task Management API built with **Laravel 11** and **MySQL**.

Built for the **Cytonn Technologies Software Engineering Internship** coding challenge.

---

## Tech Stack

- PHP 8.3
- Laravel 11
- MySQL 8
- OOP Service Layer pattern (TaskService)

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── TaskController.php       # Thin controller — delegates to service
│   └── Requests/
│       ├── StoreTaskRequest.php     # Validation for task creation
│       └── UpdateTaskStatusRequest.php
├── Models/
│   └── Task.php                     # Eloquent model with scopes
└── Services/
    └── TaskService.php              # All business logic lives here (OOP)

database/
├── migrations/
│   └── 2026_03_28_000001_create_tasks_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   └── TaskSeeder.php
└── task_manager_dump.sql            # SQL dump for direct import

routes/
└── api.php                          # All API routes
```

---

## Running Locally

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL 8+

### Steps

```bash
# 1. Clone the repo
git clone https://github.com/lagathub/task-manager-api.git
cd task-manager-api

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
DB_DATABASE=task_manager
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

# 5. Run migrations
php artisan migrate

# 6. (Optional) Seed sample data
php artisan db:seed

# 7. Start the development server
php artisan serve
# API is now available at http://localhost:8000/api
```

### Alternative: Import SQL dump directly

```bash
mysql -u root -p task_manager < database/task_manager_dump.sql
```

---

## Deploying to Railway

1. Push your project to GitHub
2. Go to [railway.app](https://railway.app) → New Project → Deploy from GitHub
3. Add a **MySQL** plugin to the project
4. Set the following environment variables in Railway:
   ```
   APP_KEY=           # generate with: php artisan key:generate --show
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=           # from Railway MySQL plugin
   DB_PORT=3306
   DB_DATABASE=       # from Railway MySQL plugin
   DB_USERNAME=       # from Railway MySQL plugin
   DB_PASSWORD=       # from Railway MySQL plugin
   ```
5. Add a `Procfile` in root:
   ```
   web: php artisan serve --host=0.0.0.0 --port=$PORT
   ```
6. Run migrations via Railway shell:
   ```bash
   php artisan migrate --force
   ```

---

## API Endpoints

Base URL: `http://localhost:8000/api`

---

### 1. Create Task

**POST** `/api/tasks`

**Rules:**
- `title` + `due_date` combination must be unique
- `due_date` must be today or a future date
- `priority` must be `low`, `medium`, or `high`

**Request Body:**
```json
{
  "title": "Set up CI/CD pipeline",
  "due_date": "2026-04-01",
  "priority": "high"
}
```

**Success Response (201):**
```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Set up CI/CD pipeline",
    "due_date": "2026-04-01",
    "priority": "high",
    "status": "pending",
    "created_at": "2026-03-28T10:00:00.000000Z",
    "updated_at": "2026-03-28T10:00:00.000000Z"
  }
}
```

**Error — Duplicate title+date (422):**
```json
{
  "message": "The title has already been taken.",
  "errors": {
    "title": ["A task with this title already exists for the given due date."]
  }
}
```

---

### 2. List Tasks

**GET** `/api/tasks`

Sorted by priority (high → medium → low), then `due_date` ascending.

**Optional query param:** `?status=pending|in_progress|done`

**Example:**
```
GET /api/tasks
GET /api/tasks?status=pending
```

**Success Response (200):**
```json
{
  "message": "Tasks retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Set up CI/CD pipeline",
      "due_date": "2026-04-01",
      "priority": "high",
      "status": "pending"
    }
  ]
}
```

**No tasks found (200):**
```json
{
  "message": "No tasks found.",
  "data": []
}
```

---

### 3. Update Task Status

**PATCH** `/api/tasks/{id}/status`

**Rules — strict progression only:**
```
pending → in_progress → done
```
Cannot skip or revert.

**Request Body:**
```json
{
  "status": "in_progress"
}
```

**Success Response (200):**
```json
{
  "message": "Task status updated successfully.",
  "data": {
    "id": 1,
    "status": "in_progress"
  }
}
```

**Error — Invalid transition (422):**
```json
{
  "error": "Invalid status transition. 'pending' can only move to 'in_progress', not 'done'."
}
```

---

### 4. Delete Task

**DELETE** `/api/tasks/{id}`

**Rules:** Only tasks with `status = done` can be deleted.

**Success Response (200):**
```json
{
  "message": "Task deleted successfully."
}
```

**Error — Task not done (403):**
```json
{
  "error": "Only tasks with status 'done' can be deleted."
}
```

---

### 5. Daily Report (Bonus)

**GET** `/api/tasks/report?date=YYYY-MM-DD`

Returns task counts grouped by priority and status for the given date.

**Example:**
```
GET /api/tasks/report?date=2026-04-01
```

**Response (200):**
```json
{
  "date": "2026-04-01",
  "summary": {
    "high":   { "pending": 2, "in_progress": 1, "done": 0 },
    "medium": { "pending": 1, "in_progress": 0, "done": 3 },
    "low":    { "pending": 0, "in_progress": 0, "done": 1 }
  }
}
```

---

## Business Rules Summary

| Rule | Implementation |
|------|---------------|
| Unique title + due_date | DB unique constraint + Laravel Form Request validation |
| due_date ≥ today | `after_or_equal:today` validation rule |
| Status progression (no skip/revert) | `TaskService::advanceStatus()` — checks exact next step |
| Delete only done tasks | `TaskService::deleteTask()` — returns 403 otherwise |
| Sort: priority desc, due_date asc | Eloquent scope using `FIELD()` + `orderBy` |

---

## Design Decisions

### OOP Service Layer
Business logic is isolated in `TaskService`, keeping controllers thin.
This makes the code easier to test, maintain, and scale.

```
Request → Controller (HTTP only) → TaskService (logic) → Eloquent (DB)
```

### Form Requests
Validation is handled by dedicated `StoreTaskRequest` and `UpdateTaskStatusRequest`
classes, keeping controllers clean and validation reusable.

### Route ordering
The `/api/tasks/report` route is defined **before** `/api/tasks/{task}` to prevent
Laravel's route model binding from treating `"report"` as a task ID.

---

## Author

Herbert — BSc Computer Science, Kenyatta University  
GitHub: [lagathub](https://github.com/lagathub)
