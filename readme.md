# TrustPay
## Quick Start

- Clone repo
- Run `composer install`
- Run `php artisan jwt:generate`
- Configure `.env` file for authenticating via database
- Set the `API_PREFIX` parameter in .env file to api.
- Run `php artisan migrate --seed`

## Live Test

- Run a PHP built in server from root project:

```sh
php -S localhost:8000 -t public/
```

Or via artisan command:

```sh
php artisan serve
```

To authenticate a user, make a `POST` request to `/api/auth/login` with parameter as mentioned below:

```
email: test@trustpay.com
password: test123
```

Request:

```sh
curl -X POST -F "email=test@trustpay.com" -F "password=test123" "http://localhost:8000/api/auth/login"
```

Response:

```
{
  "success": {
    "message": "token_generated",
    "token": "a_long_token_appears_here"
  }
}
```

- With token provided by above request, you can check authenticated user by sending a `GET` request to: `/api/auth/user`.

Request:

```sh
curl -X GET -H "Authorization: Bearer a_long_token_appears_here" "http://localhost:8000/api/auth/user"
```

Response:

```
{
    "message": "authenticated_user",
    "data": {
        "id": 1,
        "role_id": 1,
        "seller_id": null,
        "deliveryman_id": null,
        "firstname": "Test",
        "lastname": "User",
        "phone_number": "08020000000",
        "email": "test@trustpay.com",
        "birthday": "0000-00-00",
        "device_type": "web browser",
        "device_version": "41.0.1",
        "deleted_at": null,
        "created_at": null,
        "updated_at": null
    }
}
```

- To refresh user token, simply send a `PATCH` request to `/api/auth/refresh`.
- to invalidate token by sending a `DELETE` request to `/api/auth/invalidate`.
