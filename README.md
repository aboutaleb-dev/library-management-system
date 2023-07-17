## About Library Management System

Library Management System is a personal project to show my skills I built this project with Laravel.

Short description:
Doing the process for borrowing book library by users, adding book and its stock and etc. by admins, adding cost for each book if it returned with delay, user can only have limited number of book simultaneously, and more configs that can be added via .env file that can be found in [.env File](#.env-file) section.
[Installation](#installation)
[Postman Api](#postman-api)

## User Features

- **Signup by email with validating the email first. validation will be done by sending OTP code to user email.**
- **Set password After validation is done.**
- **Login with Email and password.**
- **Reset password.**
- **Borrow book from library.**
- **Logout.**

## Admin Features

- **Add/update/delete book.**
- **Add borrowed book returned.**
- **Get user costs (this will return total costs plus each book cost).**
- **Activate/deactivate user.**

## .env File

I have prepared some of these field for more security, more control over app and optimizing database (means not to have many unnecceray rows). This fields are **required** for app to perform correctly.
At the end of each line I added my reasons.Wich will be from these items:

- More Security
- More Control
- Optimization

- **MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_ENCRYPTION** Are configs that you can get from your email sending service.
- **QUEUE_CONNECTION** Is set to database beacuse this app will send email by queue if you want to change your queue database to redis or etc. you shoud change this field.
- **ADMIN_TOKENS_NUMBER** Number of tokens admin can obtain if admin wants another when having max number token admin first token will be deleted. ***More Security*** - ***Optimization***
- **ADMIN_TOKENS_EXPIRES_AFTER** Number of hours that admin token is valid. ***More Security***
- **ADMIN_LOGIN_ROUTE_LIMIT_AFTER** Number of times that admin is allowed to request to login if admin tries more than this number in a minute will get 429 Too Many Attempts error it will be done by Rate Limiter in Laravel. ***More Security***
- **ADMIN_ADDING_STRING_FILEDS_MAX_LENGHT** Max size of string fields that admin can add for example in add book this field: name. ***More Security***
- **USER_SIGNUP_STRING_FILEDS_MAX_LENGHT** Max size of string fields that user can signup with for example this field: email. ***More Security***
- **USER_OTP_EXPIRES_AFTER** Number of minutes that user otp is valid. ***More Security***
- **USER_RESED_OTP_ROUTE_LIMIT_AFTER_TIMES** Number of times that user is allowed to request resend otp if user tries more than this number in **USER_RESED_OTP_ROUTE_LIMIT_AFTER_MINUTES** minutes will get 429 Too Many Attempts error it will be done by Rate Limiter in Laravel. This field and **USER_RESED_OTP_ROUTE_LIMIT_AFTER_MINUTES** are supplement. ***More Security***
- **USER_RESED_OTP_ROUTE_LIMIT_AFTER_MINUTES** Number of minutes for **USER_RESED_OTP_ROUTE_LIMIT_AFTER_TIMES** number. This field and **USER_RESED_OTP_ROUTE_LIMIT_AFTER_TIMES** are supplement. ***More Security***
- **USER_TOKENS_NUMBER** Number of tokens user can obtain if user wants another when having max number token user first token will be deleted. ***More Security*** - ***Optimization***
- **USER_TOKENS_EXPIRES_AFTER** Number of hours that user token is valid. ***More Security***
- **USER_VERIFY_EMAIL_ROUTE_LIMIT_AFTER** Number of times that user is allowed to request to verify email if user tries more than this number in a minute will get 429 Too Many Attempts error. This will be done by Rate Limiter in Laravel. ***More Security***
- **USER_LOGIN_ROUTE_LIMIT_AFTER** Number of times user is allowed to request to login if user tries more than this number in a minute will get 429 Too Many Attempts error. This will be done by Rate Limiter in Laravel. ***More Security***
- **USER_BORROW_TIME** Time in days that user can hold borrowed book after that book should be returned. ***More Control***
- **USER_BORROW_NUMBER** Number of books user is allowed to borrow. ***More Control***
- **USER_EXPIRED_BORROW_EMAIL_SEND_BEFORE** Number of days before book borrow time expires that expiration email will be sent to user. This will be done by Task Scheduling in Laravel. ***More Control***
- **USER_EXPIRED_BORROW_COST_AFTER** Cost in dollar of each days that user return the borrowed book after expiration time. ***More Control***
- **BOOK_IMAGE_MAX_SIZE** Max size of book image field that admin can upload. ***More Control***
- **BOOK_IMAGE_ROUTE** Folder name inside ./storage/app that admin uploaded book images will goes to. ***More Control***
- **BOOK_MAX_STOCK** Max number of book stocks that admin can add for each book. ***More Control***

## Optimization

Another thing that I did for optimization is that I set schedules to delete expired tokens and OTPs.
This will be done by Task Scheduling in Laravel.

## Email Markdown Templates

I created email templates with help of Markdown Mailables in Laravel
Blade files are in ./resources/views/emails folder.

## Some of Laravel skills I worked with

- Sanctum
- Rate Limiting
- Mail
- Task Scheduling

## License

The Library Management System is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Installation

If you want to start using this code follow the stpes:

Clone this repository

```
git clone https://github.com/aboutaleb-dev/library-management-system.git
```

Go to project folder

```
cd library-management-system
```

Install composer dependencies

```
composer install
```

Copy .env.example to .env

```
cp .env.example .env
```

Generate app key

```
php artisan key:generate
```

Run migrations

```
php artisan migrate
```

Run local server

```
php artisan serve
```

Run queue this command is necassary because emails are sent by queue

```
php artisan queue:work
```

Run schedules

```
php artisan schedule:work
```

Seed database

```
php artisan db:seed
```

this will add:

1 Admin :
username: admin
password: #AfMBj3E5yE^8ye&3cD9i4g^X!ULTHM@

3 books:
random name, image, etc.

**Now you can start testing api!**

If you want to fresh and seed database

```
php artisan migrate:fresh --seed
```

## Postman Api

You can find postman collection in **postman/collections** directory, file is named **Library Management System.json**.
This postman collection has all of api requests with their method, body, etc.
