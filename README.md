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

This will publish the required configuration, assets and seeders from this package to your project and run migrations and seeders.

If you do not wish to run the seeders and would rather do it manually, you can use the `--empty` option when installing.

```bash
php artisan admin:install --empty
```

If you wish to forcibly overwrite all files, you may use the `--force` option when installing.

```bash
php artisan admin:install --force
```

If you have recently updated Laravel Admin package, you may use the `--update` option when installing. This will only overwrite assets and seeders and skip some options entirely.

```bash
php artisan admin:install --update
```

Note: If you are not using the `--update` option when installing, you will be prompted to replace the `database/seeders/DatabaseSeeder` with the one that comes with LaravelAdmin. It is recommend you do this when installing Laravel Admin for the first time, as it will make running seeders easier with the `php artisan db:seed` command.

#### Default Roles and Users

By Default, Laravel Admin installs two Roles `Dev` and `Admin`. Dev is an unrestricted Role and ignores all permission settings (can access everything). Admin is given most administrative permissions not involving some super-level permissions that manages Admin Panel critical data such as deleting Settings and running Commands. Dev can grant Admin any permissions if they want and Admin can pass on whatever permission they have to any other new Roles that are created afterwards.

If you did not use the `--empty` option when installing, a default `Dev` User is also created for initital login. The following credentials can be used to login.

Role          | Username       | Password
| :---        | :---           | :---
Developer     | dev            | 123456

Under the same install conditions, you will be asked if you want to create an `Admin` user if none exists. If you proceed, the basic user data will need to be provided to continue.

#### Extending the Laravel Admin User Model and generating a Policy
When not installing with the `--update` option, the install script will automatically try to set the default Laravel User Model (`App\Models\User`) as a child to the User Model (`Samik\LaravelAdmin\Models\User`) provided by Laravel Admin and then generate a Policy for this User model. If this process fails for some reason, you will need to do this manually and also generate a Policy for everything to work seamlessly. 

To do this, you can change the User model to extend the Laravel Admin's User Model:

```
namespace App\Models;

class User extends \Samik\LaravelAdmin\Models\User
{

}
```

And then generate an UserPolicy with the following command:

```bash
php artisan make:xpolicy UserPolicy
```

If the `App\Policies\UserPolicy` somehow exists before this, it won't get created or replaced. You will then need to ensure that the policy class extends Laravel Admin's Crud Policy (`Samik\LaravelAdmin\Policies\CrudPolicy`) class:

```
namespace App\Policies;

use Samik\LaravelAdmin\Policies\CrudPolicy;

class UserPolicy extends CrudPolicy
{
    
}
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

This will update the `database/data/permissions.json` and `database/data/menu-items.json` files which will then be used to run the seeders to populate the database. 

If you want to skip seeding initially you can omit the `--seed` option and then make adjustments to these JSON files and run:

```bash
php artisan db:seed --class=PermissionSeeder
```

for seeding the Permissions manually (using an updateOrCreate operation)

```bash
php artisan db:seed --class=MenuItemSeeder
```

for seeding the MenuItems manually (using a Truncate and Insert operation)

If you run these commands, you can expect to have:

* A `MyModule` class in `App\Models` namespace
* A `MyModuleController` class in `App\Http\Controllers\Admin` namespace
* A `MyModulePolicy` class in `App\Policies` namepsace
* A `*_create_my_modules_table` file in `database\migrations`
* A `my-modules` route MenuItem entry in `database\data\menu-items.json`
* A `MenuItem` Permission entry in `database\data\permissions.json`
* Support for routes `my-modules`, `my-modules/{id}`, `my-modules/new`, `my-modules/{id}/edit`, `my-modules/{id}/delete` and more, if Auto Routing is enabled

### Using the HasFileUploads Trait

Laravel Admin comes with a Trait that automatically handles file uploads and deletions. Use the `Samik\LaravelAdmin\Traits\HasFileUploads` trait in your model and specify the location where files will be stored, and the model will store the file upon being created. Upon being deleted or updated, the older stored files will be deleted as well.

```
namespace App\Models;

use Samik\LaravelAdmin\Models\BaseModel;
use Samik\LaravelAdmin\Traits\HasFileUploads;

class MyModel extends BaseModel
{
    // use the trait
    use HasFileUploads;

    // specify the upload field
    protected $uploadFields = ['myfile' => ['folder' => 'my-models', 'disk' => 'public']];

    // specify the field type options for form generation and value display (optional)
    public static function elements()
    {
        return [
            'myfile' => [
                'type' => 'file',,
                'displayAs' => 'image'
            ],
        ];
    }
}
```
In this example, the model will expect a field input named 'myfile' as part of the request. Base64 encoded fields will also be handled by this trait. In the `$uploadFields` configuration if a folder is not specified, a folder named `uploads` will be assumed. All files that are uploaded will be stored in a sub-folder inside the specified folder grouped by the current Year and Month to make folder navigation easier.

If the file gets uploaded but you cannot access it via urls, try to generate the symbolic links:

```bash
php artisan storage:link
```

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
