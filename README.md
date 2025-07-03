# Themepaste Secure Admin

This WordPress plugin enhances the security and customization of the WordPress admin area. It provides features such as changing the default login URL, hiding the admin bar, and more, with a modular, object-oriented architecture that is easy to extend.

## Core Technologies

- **PHP 8.0+**
- **WordPress 6.0+**
- **Node.js** (for frontend asset management)
- **Webpack** (for bundling JavaScript and CSS)
- **React** (for building interactive admin interfaces)
- **Composer** (for PHP dependency management)

## Project Structure

```
/
├── app/                    # Main application logic
│   ├── Classes/            # Core classes (Admin, Common, etc.)
│   │   ├── Features/       # Individual, self-contained feature classes
│   │   └── FeatureManager.php # Loads and initializes all features
│   ├── Interfaces/         # PHP interfaces (e.g., FeatureInterface)
│   └── ...
├── assets/                 # Compiled frontend assets (CSS, JS)
├── spa/                    # React source code for single-page applications
├── vendor/                 # Composer dependencies
├── node_modules/           # Node.js dependencies
├── composer.json           # PHP dependencies
├── package.json            # Node.js dependencies and scripts
├── webpack.config.js       # Webpack configuration
└── themepaste-secure-admin.php # Main plugin file
```

## Development Workflow

### Prerequisites

- **Node.js & npm:** [Install Node.js](https://nodejs.org/en/download/)
- **Composer:** [Install Composer](https://getcomposer.org/download/)

### Initial Setup

1.  **Clone the repository** into your WordPress `plugins` directory.
2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

### Frontend Development

This project uses Webpack to manage and bundle frontend assets. The React source files are located in the `spa/` directory.

-   **To start the development server with hot-reloading:**

    ```bash
    npm start
    ```

-   **To build the assets for production:**

    ```bash
    npm run build
    ```

-   **To watch for changes and automatically rebuild:**

    ```bash
    npm run watch
    ```

## Architecture Overview

The plugin follows a modern, object-oriented architecture designed for scalability and maintainability.

### Feature-Oriented Design

Each distinct feature (e.g., hiding the admin bar, custom login URL) is encapsulated in its own class within the `app/Classes/Features/` directory. This keeps the codebase organized and makes it easy to add, remove, or modify features without affecting the rest of the plugin.

### Feature Loading

The `FeatureManager` class is responsible for automatically loading and initializing all active features. To add a new feature, you simply need to:

1.  Create a new class in the `Features` directory that implements the `FeatureInterface`.
2.  Add your new feature class to the `$features` array in `FeatureManager.php`.

This design follows the Open/Closed Principle, allowing the plugin to be extended without modifying its core files.

### Autoloading

The plugin uses Composer's PSR-4 autoloader to automatically load all PHP classes. This eliminates the need for manual `require` or `include` statements.

## How to Add a New Feature

1.  **Create a new feature class** in `app/Classes/Features/` (e.g., `MyNewFeature.php`).
2.  **Implement the `FeatureInterface`** in your new class.
3.  **Add your hooks and logic** inside the `register_hooks()` method.
4.  **Register your new feature** in the `FeatureManager` class.

By following these conventions, you can ensure that your new feature integrates seamlessly into the existing architecture.