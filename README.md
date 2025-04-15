# Laravel Nova HasMany Searchable Field

A Laravel Nova field that provides a searchable interface for has-many relationships with the ability to create new related resources.


![screenshot of the search relations tool](./screenshot.png)

## Requirements

- PHP 8.2+
- Laravel 11.x | 12.x
- Laravel Nova 4.x | 5.x

## Installation

You can install the package via composer:

```bash
composer require metasync-site/nova-has-many-searchable
```

## Usage

```php
use MetasyncSite\NovaHasManySearchable\HasManySearchable;

public function fields(NovaRequest $request)
{
    return [
        HasManySearchable::make('Coupons')
            ->relationshipConfig(
                resourceClass: CouponsResource::class,
                foreignKey: 'GoodsDataId',
                displayCallback: function ($coupon) {
                    return "{$coupon?->Name} (Type: {$coupon->Type})";
                }
            )
            ->withCreateButton(true, 'Create New Coupon'),
    ];
}
```

## Features

- 🔍 Searchable interface for has-many relationships
- ✨ Custom display formatting
- ➕ Optional "Create New" button with modal
- 🎨 Dark mode support
- 🎯 Type-safe implementation

## Configuration

### Display Callback

You can customize how each option is displayed using the `displayCallback`:

```php
HasManySearchable::make('Coupons')
    ->relationshipConfig(
        resourceClass: CouponsResource::class,
        foreignKey: 'GoodsDataId',
        displayCallback: fn($model) => "{$model->name} ({$model->code})"
    )
```

### Create Button

Enable the create button with an optional custom label:

```php
HasManySearchable::make('Coupons')
    ->withCreateButton(true, 'Add New Coupon')
```

## Security Vulnerabilities

If you discover any security vulnerabilities, please email metasyncsite@gmail.com

## Credits
- [Metasync](https://github.com/metasyncSite)

