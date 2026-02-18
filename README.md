# AICI-UMG Platform

Official website for the Artificial Intelligence Center Indonesia (AICI)

## Local Docker Setup (Recommended)

### 1. Prepare environment

```bash
cp .env.example .env
```

### 2. Build and run services

```bash
docker compose up -d --build
```

Services started:
- `nginx` (app entrypoint) on `http://localhost:8000`
- `app` (`php-fpm`)
- `db` (MariaDB 10.11) on host port `3307`
- `redis` on host port `6380`
- `queue` worker
- `scheduler`

Default Docker runtime mode:
- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`

### 3. Initialize app

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
```

Optional: seed admin/test user only

```bash
docker compose exec app php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder --force
```

### 4. Useful commands

```bash
# open shell inside app container
docker compose exec app sh

# artisan command
docker compose exec app php artisan route:list

# check queue connection is redis
docker compose exec app php artisan tinker --execute="echo config('queue.default');"

# logs
docker compose logs -f --tail=200

# stop
docker compose down
```

### 5. Makefile shortcuts

```bash
make up
make init
make ps
make logs
make down
```

## About This Project

AICI-UMG is a web-based platform that serves as the primary digital presence for the Artificial Intelligence Center Indonesia. The platform provides comprehensive information about AI and robotics education programs, facilitates student enrollment through an integrated placement test system, and serves as a hub for research publications and community engagement.

### Key Features

**Public Information**

- Program catalog with detailed course information
- Facility showcase and laboratory equipment
- Research publications and articles
- Gallery of activities and achievements
- Contact and location information

**Student Services**

- Online placement test system
- Program recommendations based on test results
- Enrollment and registration workflow
- Integrated payment processing via Xendit

**Administrative Tools**

- Content management dashboard
- Student data management
- Test question bank administration
- Payment tracking and reporting

## Contact

**Artificial Intelligence Center Indonesia (AICI)**  
Pertamina Multidisciplinary Research Laboratory Building  
Faculty of Mathematics and Natural Sciences  
University of Indonesia, 4th Floor  
Depok, West Java 16424
Phone: 0821-1010-3938

## License

Proprietary - All rights reserved by Artificial Intelligence Center Indonesia (AICI)
