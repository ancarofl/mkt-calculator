# MKT Calculator![image](https://github.com/user-attachments/assets/cad6d64c-6ab8-4e99-9f21-426a4a8e6db8)
![image](https://github.com/user-attachments/assets/a23eee2d-68a7-4581-9c64-b3aad7b3255d)![image](https://github.com/user-attachments/assets/8e3599a4-6ebc-4be2-887f-217380e01f2b)


## Prerequisites

- **PHP 8.3**
- **Composer**
- **Symfony CLI** 
- **MySQL** 

## Getting Started

### 1. Clone the Repository
```bash
git clone https://github.com/ancarofl/mkt-calculator.git
cd mkt-calculator
```

### 2. Environment Setup
```bash
# Copy .env.example and call it .env
```

### 3. Install Dependencies
```bash
composer install
```

### 4. Database Setup
```bash
# Set your database details in .env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

# Create the database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate
```


### 5. Start Development Server
```bash
symfony serve:start
```
Access: http://127.0.0.1:8000

