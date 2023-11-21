## Filament issues

### Installation
* composer install
* migrate
* seed

Seed will create 3 users (one per role):
admin@mail.com secret
federation@mail.com secret
client@mail.com secret

### Issues

1. Problem with footer image and sticky menu
Steps:

* Create custom theme: php artisan make:filament-theme
* Add resources/css/filament/admin/theme.css to vite.config.js
* Add theme to AdminPanelProvider: ->viteTheme('resources/css/filament/admin/theme.css')
* Add filament directory to theme/tailwind.config.js:
   './resources/views/vendor/filament-panels/components/*.blade.php',

2. Redirects between 3 panels
3. Translatable fields in selects and indicators
