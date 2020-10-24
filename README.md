# Makise-Co
## Описание
Данный фреймворк создан с применением современных подходов разработки на PHP.
Для обеспечения эффективного использования памяти и высоких нагрузок.

Почему Makise? Kurisu Makise вдохновила на создание данного инструмента.

## Области применения
Фреймворк создается преимущественно для SOA (Service Oriented Architecture),
а тажке для Микросервисной архитектуры.

Основная цель - обеспечить удобную разработку HTTP REST API (в будущем не только REST).

## Особенности
* Данный фреймворк не имеет магии, кроме DI
* Используется строгая типизация настолько, насколько это возможно (в пределах разумного)
* Нет глобальных контекстов
* Полностью неблокирующее I/O (корутины)
* PSR-совместим, но расширяет функциональность для обеспечения работы
долгоживущего приложения (Long-Live/Long-Running)
* Фреймворк реализует API, схожее с Laravel
* Фреймворк использует общепринятые библетеки, такие как:
    * symfony/console
    * laminas/laminas-diactoros
    * symfony/event-dispatcher
    * monolog/monolog
    * phpdi/phpdi
    * vlucas/phpdotenv

## Требования
* PHP 7.4+
* Swoole 4.4+
* Linux

## Структура HTTP стека
1. HTTP стек реализует стандарты PSR-7 и PSR-15
2. Маршрутизация HTTP запросов осуществляется Pipeline-ориентированным путем (п.1)
3. Порядок вызовов:
    1. RequestHandler
    2. Global Middlewares
    3. ExceptionHandlerMiddleware
    4. RouteDispatchHandler
    5. Route group middlewares
    6. RouteInvokeHandler
    7. Конечный обработчик запроса (контроллер)

## Конфигурация
Конфиг файлы расположены в директории `config`.
Путь к папке можно переопределить в bootstrap.php (сейчас это app.php)

## Сервис провайдеры (инъекция через DI)
Необходимо реализовать метод в конфиге метод интерфейса `MakiseCo\Config\AppConfigInterface::getProviders`

## Консольные команды
Необходимо реализовать метод в конфиге метод интерфейса `MakiseCo\Config\AppConfigInterface::getCommands`

## Файлы с маршрутами
Необходимо реализовать метод в конфиге метод интерфейса `MakiseCo\Config\AppConfigInterface::getHttpRoutes`

## Глобальные Middleware (CORS, AccessLog, etc...)
Необходимо реализовать метод в конфиге метод интерфейса `MakiseCo\Config\AppConfigInterface::getGlobalMiddlewares`

При использовании глобальных Middleware нужно самостоятельно обрабатывать исключения (или не допускать их вовсе),
так как они выполняются до exception handler'а.
