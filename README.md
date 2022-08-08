# LaraBreeze2fa
2 factor authentication Laravel8 Breeze easiest way with session

Storing second passcode in session - time to live code = session live time, use db only for debug (i.e. email not delivered)
No additional guard and controllers, only routes and templates

## Requires
laravel/framework ^8.75
laravel/breeze ^1.9.2

## Installation
1. Make backup original auth controller
```sh
mv app/Http/Controllers/Auth/AuthenticatedSessionController.php app/Http/Controllers/Auth/AuthenticatedSessionController.old
```
2. Download archive
```sh
wget https://github.com/Kvach-Lukich/LaraBreeze2fa/archive/refs/heads/main.zip
```
3. Move files to laravel folder
```sh
mv LaraBreeze2fa-main/* path/to/laravel/folder/
rm LaraBreeze2fa-main
```
4. Configure email if already not
5. Add fields to user table or start megration
~~~~sql
ALTER TABLE `users` ADD `code` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `remember_token`, ADD `no2fa` BOOLEAN NULL DEFAULT NULL AFTER `code`; 
~~~~
