# Menuable

it's a menu generator based on [users permissions](https://github.com/Hans-Thomas/laravel-permission)

## Configuration

- `path` : define the endpoint to get a menu.
- `menus` : you can define the menu, each menu has
    - `key` : separate the different menus
    - `order` : order priority of item in the list
    - `permissions` : is an array that contains necessary permissions which user must has all of them

    - `title` : the title of the item
    - `icon` : the icon of the item
    - `class` : an additional class for assigning to the item
    - `link` : the route name of the item
    - `children` : if your menu has children you can define items in an array

- `middlewares` : you can apply your custom middlewares after `auth:sanctum` middleware

## Usage

### Create menus

after the configuration, you can create the defined menus using `menuable:install` command in artisan CLI. the command
accept `--fresh` option that remove all previous menus and create them.

### Get a menu

to get menu, just add your menu's key to end of the path endpoint that defined in config file