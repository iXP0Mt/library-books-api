# Library Books API

---

Для хранения конфиденциальных данных, включая секретный ключ для JWT и параметры подключения к базе данных, используется библиотека `vlucas/phpdotenv`.
Пример необходимых переменных окружения приведён в файле `.env.example`.

Аутентификация пользователей реализована с использованием JWT (JSON Web Token) на базе библиотеки `firebase/php-jwt`.

---

## Список Endpoints

- [Аутентификация](#аутентификация)
  - [Регистрация](#регистрация)
  - [Авторизация](#авторизация)
- [Промежуточная аутентификация](#промежуточная-аутентификация)
- [Получить список зарегистрированных участников](#получить-список-зарегистрированных-участников)
- [Выдать доступ к библиотеке](#выдать-доступ-к-библиотеке)
- [Получить список книг пользователя](#получить-список-книг-пользователя)
- [Создать книгу](#создать-книгу)
- [Открыть книгу](#открыть-книгу)
- [Сохранить книгу](#сохранить-книгу)
- [Удалить книгу](#удалить-книгу)
- [Восстановить удаленную книгу](#восстановить-удаленную-книгу)
- [Список книг другого пользователя](#список-книг-другого-пользователя)
- [Поиск существующих книг](#поиск-существующих-книг)
- [Сохранение найденной книги](#сохранение-найденной-книги)

---

### Аутентификация
#### Регистрация
- URL: /api/v1/auth/register
- Method: POST
- Body (JSON)

```json
{
  "login": "user_login",
  "password": "user_password",
  "password_confirm": "user_password"
}
```

- Пример ответа:

```json
{
  "status": "OK",
  "token": "DJ49r329chr8c...",
  "user_info": {
    "user_id": 324
  }
}
```

---

#### Авторизация

- URL: /api/v1/auth/login
- Method: POST
- Body (JSON)
```json
{
  "login": "user_login",
  "password": "user_password"
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "token": "DJd3sfFSR49r5342Fsd...",
  "user_info": {
    "user_id": 42
  }
}
```

---

### Промежуточная аутентификация
> Все endpoints, кроме регистрации и авторизации требуют, прохождения Auth Middleware, который должен принять JWT (Token), полученный при регистрации или авторизации, в заголовке **Authorization: Bearer**

---

### Получить список зарегистрированных участников

- URL: /api/v1/users
- Method: GET


- Пример ответа:
```json
{
  "users": [
    {
      "user_id": 1,
      "login": "user_login1"
    },
    {
      "user_id": 2,
      "login": "user_login2"
    }
  ]
}
```

---

### Выдать доступ к библиотеке

- URL: /api/v1/users/{Grantee User ID}/share
- Method: POST


- Пример ответа
```json
{
  "status": "OK",
  "message": "Access granted"
}
```

---

### Получить список книг пользователя

- URL: /api/v1/user/books
- Method: GET


- Пример ответа:
```json
{
  "books": [
    {
      "book_id": 1,
      "title": "Title Book1"
    },
    {
      "book_id": 2,
      "title": "Title Book2"
    }
  ]
}
```

---

### Создать книгу

- URL: /api/v1/user/books/create
- Method: POST
- Body (JSON):
```json
{
  "title": "Some Book Title",
  "text": "Some Book Text"
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "message": "Book created"
}
```

#### Передача текста книги файлом

**Вместо передачи текста книги строкой, можно передать текст книги файлом `.txt`**

Вместо использования body, передать `title` и `text` в виде строки и файла соответственно в `form-data`
```text
title: Some Book Title (Text)
text: book.txt (File)
```

---

### Открыть книгу

- URL: /api/v1/books/{Book ID}
- Method: GET


- Пример ответа:
```json
{
  "title": "Book Title",
  "text": "Some text of book"
}
```

---

### Сохранить книгу

- URL: /api/v1/user/books/save
- Method: PUT
- Body (JSON):
```json
{
  "book_id": 1,
  "title": "New Title",
  "text": "New Text"
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "message": "Book updated"
}
```

---

### Удалить книгу

- URL: /api/v1/user/books/delete
- Method: DELETE
- Body (JSON):
```json
{
  "book_id": 1
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "message": "Book deleted"
}
```

---

### Восстановить удаленную книгу

- URL: /api/v1/user/books/restore
- Method: PUT
- Body (JSON):
```json
{
  "book_id": 1
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "message": "Book restored"
}
```

---

### Список книг другого пользователя

- URL: /api/v1/users/{User ID}/books
- Method: GET


- Пример ответа:
```json
{
  "books": [
    {
      "book_id": 12,
      "title": "Book Title",
      "text": "Some Book Text"
    },
    {
      "book_id": 13,
      "title": "Book Title",
      "text": "Another Book Text"
    }
  ]
}
```

---

### Поиск существующих книг

- URL: /api/v1/external/search?q=SEARCH_FIELD
- Method: GET


- Пример ответа:
```json
{
  "items": [
    {
      "external_book_id": "LoURH4HD34FD",
      "title": "Example external title book from Google APIs",
      "text": "Example external text book from Google APIs"
    },
    {
      "external_book_id": "17346",
      "title": "Example external title book From MIF",
      "text": "Example external text book from MIF"
    }
  ]
}

```

### Сохранение найденной книги

- URL: /api/v1/external/save
- Method: POST
- Body (JSON):
```json
{
  "external_book_id": "LoURH4HD34FD"
}
```

- Пример ответа:
```json
{
  "status": "OK",
  "message": "Book saved"
}
```
