# Filament issues

## Installation
* composer install
* migrate
* seed

Seed will create 3 users (one per role):
* admin@mail.com secret
* federation@mail.com secret
* client@mail.com secret

## Issues

### 1. Problem with footer image and sticky menu
Steps:

* Create custom theme: php artisan make:filament-theme
* Add resources/css/filament/admin/theme.css to vite.config.js
* Add theme to the panel providers: ->viteTheme('resources/css/filament/admin/theme.css')
* Add filament directory to theme/tailwind.config.js:
   './resources/views/vendor/filament-panels/components/*.blade.php',
* Add logo.blade.php to add a custom logo with classes and force top bar height with css
* Add footer.blade.php to add image at the end. 
  * If the panel has the sidebarCollapsibleOnDesktop function, the footer won't stick to the bottom

When impersonating (with https://github.com/stechstudio/filament-impersonate), the menu breaks down

![img.png](public/readme/1.png)

Fixed with custom css block 1 in theme.css (but can this be making the next problem)

### 2. Redirects between 3 panels

We have 3 panels (One per role): 
* admin (path: /admin)
* federation (path: /federation) 
* client (path: /) 


But the login has to be the same for all: /login

To avoid logout after successful login in the Authenticate class of admin or federation in the client's login,
we change the canAccessPanel to return true
And force the redirects in an overwritten LoginResponse
Also modify the LogoutResponse to redirect to /login

But that means that both admin and federation users will go to the client's panel when going back to the site while being logged in

Where should this redirect be handled? or how to do this in a better way?

### 3. Translatable fields in selects and indicators
