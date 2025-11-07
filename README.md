## Инструкции по запуску

- Создать .env файл из файла .env.example
- Запустить контейнеры - **docker compose up -d**
- Вывести список запущенных контейнеров - **docker ps**
- Скопировать ID контейнера php-fpm, что бы зайти в него - **docker exec -it {ID} bash**
- Дальнейшие команды выполняются внутри контейнера php-fpm:
    - **composer install**
    - **chown -R www-data:www-data /application/storage /application/bootstrap/cache**
    - **chmod -R 775 /application/storage /application/bootstrap/cache**
    - **php artisan key:generate**
    - **php artisan migrate**
- Маршрут для выполнения запросов - http://localhost:50000/api/
- Создать пользователя, для получения Bearer токена, маршрут - /register. 
<br>
```
Тело запроса:

{
    "name": "Username",
    "email": "test@email.com",
    "password": "test0000"
}

Ответ:

{
    "user": {
        "name": "Username",
        "email": "test@email.com",
        "updated_at": "2025-11-07T10:34:24.000000Z",
        "created_at": "2025-11-07T10:34:24.000000Z",
        "id": 1
    },
    "token": "1|693JHcLvV9onRIHMzQSkFytpG6DYsiIqZNQgoPKT3cf71a97",
    "token_type": "Bearer"
}
```
- Полученный токен (*часть после разделителя ' | '*) необходимо передавать в заголовке **Authorization** для каждого запроса.
- При создании задачи по маршруту **POST /projects/{project_id}/tasks** необходимо убедиться, что ID пользователя, передаваемое в поле **performer**, существует в таблице **users**. После создания задачи отправляется e-mail (*посмотреть можно в /storage/logs/laravel.log*)
```
Параметры запроса:

title - string*
description - string*
status - string (planned, in_progress, done). По умолчанию - planned.
performer - integer*
completed_at - string
file - file

```

## Конечные точки
- GET /api/projects/{project_id}/tasks — получение списка задач по проекту с фильтрацией по статусу, исполнителю и дате завершения;
- POST /api/projects/{project_id}/tasks — создание новой задачи;
- GET /api/tasks/{id} — получение информации о задаче;
- PUT /api/tasks/{id} — обновление данных задачи;
- DELETE /api/tasks/{id} — удаление задачи.
