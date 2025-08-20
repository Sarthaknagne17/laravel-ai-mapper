# Laravel AI Mapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/araminco/laravel-ai-mapper.svg?style=flat-square)](https://packagist.org/packages/araminco/laravel-ai-mapper)
[![Total Downloads](https://img.shields.io/packagist/dt/araminco/laravel-ai-mapper.svg?style=flat-square)](https://packagist.org/packages/araminco/laravel-ai-mapper)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

A simple Laravel package that scans your entire project and generates a comprehensive, AI-friendly JSON map. By providing this file to an AI, you can significantly enhance its understanding of your project's architecture.

---

## ✨ Features

This package extracts and maps the following information into a single JSON file:

-   **Project Overview**: App name, Laravel, and PHP versions.
-   **Database Schema**: A list of all tables along with their columns, indexes, and foreign keys.
-   **Model Structure**: Extracts all Eloquent models, including important properties (`$fillable`, `$casts`, etc.) and their defined **Eloquent relationships**.
-   **Directory Structure**: A tree view of key Laravel directories (`app`, `routes`, `config`, etc.).
-   **Route List**: All web and API routes, including methods, URIs, and middleware.
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

For large projects, the default output can become very large. You can control the size and content of the generated map using the following options.

#### Compact Mode

Use the `--compact` flag to generate a summarized version that is significantly smaller. This mode simplifies the most verbose sections like the database schema, routes, and dependencies.

```bash
php artisan ai:map --compact
```

#### Excluding Sections

You can also completely exclude sections you don't need from the map:

-   `--no-db`: Excludes the database schema.
-   `--no-files`: Excludes the directory structure.
-   `--no-models`: Excludes the model analysis.
-   `--no-routes`: Excludes the route list.
-   `--no-deps`: Excludes composer dependencies.
-   `--no-filament`: Excludes the Filament structure.

You can combine these flags as needed:

```bash
# Generate a map without the database schema and routes
php artisan ai:map --no-db --no-routes

# Generate a compact map that also excludes the Filament structure
php artisan ai:map --compact --no-filament
```

---

## 📄 Output Sample

The generated JSON file will have a structure similar to this:

```json
{
    "projectName": "My Laravel App",
    "laravelVersion": "11.0.0",
    "databaseSchema": { "...": "..." },
    "models": [
        {
            "class": "App\\Models\\User",
            "table": "users",
            "fillable": ["name", "email", "password"],
            "relationships": {
                "posts": {
                    "type": "HasMany",
                    "related_model": "App\\Models\\Post"
                }
            }
        }
    ],
    "routes": [ "...": "..." ],
    "filament": {
        "panels": [
            {
                "id": "admin",
                "path": "admin",
                "resources": [ "UserResource" ],
                "pages": [ "Dashboard" ]
            }
        ]
    }
}
```

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📜 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).