حتماً. با توجه به قابلیت‌های جدیدی که اضافه کردیم، باید فایل `README.md` را هم آپدیت کنیم تا کاربران از این امکانات جدید مطلع شوند.

این هم نسخه به‌روز شده‌ی `README.md` به زبان انگلیسی که تمام ویژگی‌های جدید را شامل می‌شود.

-----

### README.md (Updated Version)

````markdown
# Laravel AI Mapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/araminco/laravel-ai-mapper.svg?style=flat-square)](https://packagist.org/packages/araminco/laravel-ai-mapper)
[![Total Downloads](https://img.shields.io/packagist/dt/araminco/laravel-ai-mapper.svg?style=flat-square)](https://packagist.org/packages/araminco/laravel-ai-mapper)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

A Laravel Artisan command that scans your project and generates a comprehensive, AI-friendly JSON map. This map allows AI models to understand your project's architecture, overcoming context limits and enabling more intelligent, context-aware assistance.

---

## ✨ Features

This package extracts and maps the following information into a single JSON file:

-   **Project Overview**: App name, Laravel, and PHP versions.
-   **Environment Details**: Key configuration values like environment, debug mode, and drivers for cache, queue, and session.
-   **Database Schema**: A list of all tables along with their columns, indexes, and foreign keys, with a resilient connection mechanism.
-   **Model Structure**: Extracts all Eloquent models, including properties (`$fillable`, `$casts`, etc.) and their defined **Eloquent relationships**.
-   **Route List**: All web and API routes, including methods, URIs, and middleware.
-   **Scheduled Commands**: A list of all cron jobs defined in your Console Kernel.
-   **Event Listeners**: A map of all registered events and their corresponding listeners.
-   **Composer Dependencies**: A list of packages used in the project.
-   **FilamentPHP Structure (if detected)**: Automatically discovers panels, resources, pages, and widgets.

---

## 💿 Installation

You can install the package via Composer:

```bash
composer require araminco/laravel-ai-mapper
```

---

## 🚀 Usage

To generate the project map, run the following Artisan command:

```bash
php artisan ai:map
```

This will create a file named `ai-project-map.json` in your project's root directory.

### Controlling the Output Size

You can control the size and content of the map using the following options.

#### Compact Mode

Use the `--compact` flag to generate a summarized version that is significantly smaller. This mode simplifies verbose sections like the database schema and routes.

```bash
php artisan ai:map --compact
```

#### Excluding Sections

You can also completely exclude sections from the map:

-   `--no-db`: Excludes the database schema.
-   `--no-files`: Excludes the directory structure.
-   `--no-models`: Excludes the model analysis.
-   `--no-routes`: Excludes the route list.
-   `--no-deps`: Excludes composer dependencies.
-   `--no-filament`: Excludes the Filament structure.
-   `--no-env`: Excludes environment details.
-   `--no-schedule`: Excludes scheduled commands.
-   `--no-events`: Excludes event listeners.

---

## 📄 Output Sample

The generated JSON file will have a structure similar to this:

```json
{
    "projectName": "My Laravel App",
    "laravelVersion": "12.0.0",
    "environment": {
        "environment": "local",
        "debug_mode": true,
        "cache_driver": "file",
        "queue_connection": "sync",
        "session_driver": "file"
    },
    "databaseSchema": { "...": "..." },
    "models": [
        {
            "class": "App\\Models\\User",
            "table": "users",
            "relationships": { "...": "..." }
        }
    ],
    "routes": [ "...": "..." ],
    "scheduledCommands": [
        "$schedule->command('inspire')->hourly();"
    ],
    "eventListeners": {
        "Illuminate\\Auth\\Events\\Registered": [
            "Illuminate\\Auth\\Listeners\\SendEmailVerificationNotification"
        ]
    },
    "filament": { "...": "..." }
}
```

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📜 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
````