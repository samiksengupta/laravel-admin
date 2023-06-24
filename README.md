# Laravel Admin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/samiksengupta/laravel-admin.svg?style=flat-square)](https://packagist.org/packages/samiksengupta/laravel-admin)
[![Total Downloads](https://img.shields.io/packagist/dt/samiksengupta/laravel-admin.svg?style=flat-square)](https://packagist.org/packages/samiksengupta/laravel-admin)
![GitHub Actions](https://github.com/samiksengupta/laravel-admin/actions/workflows/main.yml/badge.svg)

A quick way to set up an admin panel, API endpoints and routing for a Laravel project. You can use this package to add the following features to your project.

* A ready-to-go Admin Panel with User and Role management.
* Base Class for your Models, Controllers and Policies that will automatically handle common functionalities for CRUD operations, DataTable response generation, Validation, UI Element customization and more.
* Blade Views for Listing and Crud operations that are easily extendable on your project side.
* Bootstrap UI with Javascript based form submissions, built on top of AdminLTE theme.
* Easy to manage, Role based Permission system.
* Auto-routing support.
* Api Resource list and tester.
* Easy to use custom commands that lets you create Laravel Admin compliant Models, Migrations, Controller, Policies, Permissions and Menu Items in one shot.
* Everything can be overriden in your project for fine-tuned control.


## Installation

You can install the package via composer:

```bash
composer require samiksengupta/laravel-admin
```

## Usage

### Setting Up the Admin Panel

After the package is installed in your project and you have configured your Database connection in your `.env` file, run the following command:

```bash
php artisan admin:install
```

This is publish the required configuration, assets and seeders from this package to your project and run migrations and seeders.

If you do not wish to publish the seeders and would rather create them yourself, you can use the `--empty` option when installing.

```bash
php artisan admin:install --empty
```

If you do not wish to re-install the Admin Panel to your project or want to forcibly overwrite all files, you may use the `--force` option when installing.

```bash
php artisan admin:install --force
```

### Creating Admin Panel Modules

A module is simply a set of Model, Migration, Controller and Policy files that work togather to represent your data in the admin panel. You can create all these files in one go by using the command:

```bash
php artisan make:module MyModule
```

This will create a new Model class, a new Migration for the model's database table, a Controller and a Policy in their respective locations. You can edit the newly created Migration file to add whatever columns you want and then run migration to generate the table with the columns in them.

What sets these Classes apart from Laravel's own Model/Controller/Policy Classes is that they extend Laravel Admin's Base Classes that add extra functionality for CRUD operations, Validation, Filter Generation, DataTable Response Generation and a lot more.

If you want to quickly generate default CRUD permissions for this Model and a Menu Item entry for accessing the corresponding web route that will take you to the Model's listing page you can use the following command:

```bash
php artisan make:module MyModule --permissions --menuitems --seed
```

This will update the `database/data/permissions.json` and `database/data/menu-items.json` files which will them be used to run the seeders to populate the database. 

If you want to skip seeding initially you can omit the `--seed` option and then make adjustments to these JSON files and run:

```bash
php artisan db:seed --class=PermissionSeeder
```

for seeding the Permissions manually (using an updateOrCreate operation)

```bash
php artisan db:seed --class=MenuItemSeeder
```

for seeding the MenuItems manually (using a Truncate and Insert operation)

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email samiksengupta@hotmail.com instead of using the issue tracker.

## Credits

-   [Samik Sengupta](https://github.com/samiksengupta)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
