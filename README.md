# Forum Laravel React

A modern forum application built with Laravel 12 and React (Inertia.js), featuring full CRUD operations for threads, subforums, and posts.

## Features

- **User Authentication** - Laravel Breeze authentication system
- **Thread Management** - Create, read, update, and delete discussion threads
- **Subforum System** - Organize threads into categories (subforums)
- **Post/Reply System** - Users can reply to threads
- **Role-Based Access** - Admin and user roles with proper authorization
- **Slug-Based URLs** - SEO-friendly URLs for threads and subforums
- **Modern UI** - Built with React, Tailwind CSS, and Inertia.js

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x and npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/Forum-Laravel-React.git
   cd Forum-Laravel-React
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   
   For SQLite (default):
   ```bash
   touch database/database.sqlite
   ```
   
   Or update `.env` for MySQL/PostgreSQL:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=forum
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```
   
   In another terminal:
   ```bash
   npm run dev
   ```

9. **Visit the application**
   - Open http://localhost:8000 in your browser
   - Register a new account or use existing credentials

## Creating an Admin User

To create an admin user, you can use Laravel Tinker:

```bash
php artisan tinker
```

Then run:
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
```

## Project Structure

```
Forum-Laravel-React/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ThreadController.php      # Thread CRUD operations
│   │   │   ├── SubforumController.php    # Subforum CRUD operations
│   │   │   └── PostController.php        # Post/Reply CRUD operations
│   │   └── Requests/
│   │       ├── StoreThreadRequest.php    # Thread validation
│   │       ├── UpdateThreadRequest.php
│   │       ├── StoreSubforumRequest.php
│   │       ├── UpdateSubforumRequest.php
│   │       ├── StorePostRequest.php
│   │       └── UpdatePostRequest.php
│   └── Models/
│       ├── Thread.php                    # Thread model
│       ├── Subforum.php                  # Subforum model
│       ├── Post.php                      # Post/Reply model
│       └── User.php                      # User model with roles
├── database/
│   └── migrations/                       # Database migrations
├── resources/
│   └── js/
│       ├── Pages/Forum/                  # React/Inertia pages
│       └── Components/                   # Reusable React components
└── routes/
    └── web.php                           # Application routes
```

## CRUD Operations

### Threads
- **Create**: `GET /threads/create` → `POST /threads`
- **Read**: `GET /threads/{slug}`
- **Update**: `GET /threads/{slug}/edit` → `PATCH /threads/{slug}`
- **Delete**: `DELETE /threads/{slug}`

### Subforums
- **Create**: `GET /subforums/create` → `POST /subforums` (Admin only)
- **Read**: `GET /subforums/{slug}`
- **Update**: `GET /subforums/{slug}/edit` → `PATCH /subforums/{slug}` (Admin only)
- **Delete**: `DELETE /subforums/{slug}` (Admin only)

### Posts (Replies)
- **Create**: `POST /threads/{threadSlug}/posts`
- **Update**: `GET /threads/{threadSlug}/posts/{post}/edit` → `PATCH /threads/{threadSlug}/posts/{post}`
- **Delete**: `DELETE /threads/{threadSlug}/posts/{post}`

## Authorization

- **Threads**: Only the author or admin can edit/delete
- **Posts**: Only the author or admin can edit/delete
- **Subforums**: Only admins can create/edit/delete

## Testing

Run the test suite:

```bash
php artisan test
```

## Technologies Used

- **Backend**: Laravel 12
- **Frontend**: React 18, Inertia.js
- **Styling**: Tailwind CSS
- **Authentication**: Laravel Breeze
- **Database**: SQLite (default, configurable)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

- GitHub: [@DeivisStasuls](https://github.com/DeivisStasuls/Forum-Laravel-React)

##  Acknowledgments

- Laravel Framework
- Inertia.js
- React Community
- Tailwind CSS

---

**Note**: This is a learning project. For production use, posible additional features like:
- Pagination
- Search functionality
- Email notifications
- Image uploads
- Rich text editor
- Rate limiting
