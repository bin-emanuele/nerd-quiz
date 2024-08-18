## About Nerd-Quiz

This is a personal project to test out Laravel 11/Laravel Reverb/Inertiajs.

## How to run
After configuring the base settings in the `.env` file, copying the defaults from the `.env.example` file install all the package dependencies to run/build the application:

```bash
composer install
npm run install
```

Run the following all at the same time:
```bash
php artisan serve
php artisan reverb:start
php artisan queue:work
npm run dev
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
