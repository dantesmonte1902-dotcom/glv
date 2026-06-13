# GLV

GLV is an API-first, multi-city premium delivery platform for Bosnia and Herzegovina, starting with Sarajevo and designed to expand to Mostar, Tuzla, and additional cities.

## Architecture

Before coding, the platform is designed around these rules:

- API First Architecture
- Thin Controllers + Service Layer Pattern
- Queue-based heavy operations
- Event and Listener driven workflows
- Multi-city and multi-branch support
- Future-ready verticals (`food`, `market`, `pharmacy`)

## Implemented foundation

- Real Laravel 12 application skeleton
- PostgreSQL, Redis, queue, and broadcasting-ready environment defaults
- Dockerized local stack for app, nginx, PostgreSQL, and Redis
- Sanctum-based API auth scaffold
- Core domain models for cities, restaurants, branches, users, couriers, orders, order items, and assignments
- Order lifecycle endpoints for create, view, approve, reject, assign, accept, reject, and deliver
- Courier availability and live location update APIs
- Admin APIs for city, restaurant, branch, and courier activation management
- Feature tests for auth and order lifecycle scenarios

## Main API areas

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`
- `POST /api/v1/orders`
- `GET /api/v1/orders/{order}`
- `POST /api/v1/orders/{order}/approve`
- `POST /api/v1/orders/{order}/reject`
- `POST /api/v1/orders/{order}/assign-courier`
- `POST /api/v1/orders/{order}/accept-courier`
- `POST /api/v1/orders/{order}/reject-courier`
- `POST /api/v1/orders/{order}/deliver`
- `PATCH /api/v1/courier/location`
- `PATCH /api/v1/courier/availability`
- `GET /api/v1/admin/cities`
- `POST /api/v1/admin/cities`
- `PATCH /api/v1/admin/cities/{city}`
- `GET /api/v1/admin/restaurants`
- `POST /api/v1/admin/restaurants`
- `PATCH /api/v1/admin/restaurants/{restaurant}`
- `GET /api/v1/admin/restaurants/{restaurant}/branches`
- `POST /api/v1/admin/restaurants/{restaurant}/branches`
- `PATCH /api/v1/admin/branches/{branch}`
- `PATCH /api/v1/admin/couriers/{courier}/activation`

## Local development

```bash
cp .env.example .env
docker compose up --build
```

Then run migrations and tests inside the app container:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
```
