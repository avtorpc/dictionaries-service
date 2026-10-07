# ONMI Backend — dictionaries service

## Содержание

- [Структура проекта](#struktura)
- [Пояснения по слоям](#poyasneniya)
- [Последовательность сборки](#posledovatelnost)


## <a id="struktura"></a>Структура проекта
```
src/
├── Application
│   ├── Dictionaries
│   │   ├── DTO
│   │   │   ├── ApplicationSettingDto.php
│   │   │   ├── CountryDto.php
│   │   │   ├── RoleCountryDto.php
│   │   │   └── RoleDto.php
│   │   └── DictionaryService.php
│   └── Settings
│       └── FrontendSettingsService.php
├── Controller
│   ├── Api
│   │   ├── Dictionaries
│   │   │   └── DictionariesController.php
│   │   └── Settings
│   │       └── SettingsController.php
│   └── ApiController.php
├── Domain
│   ├── Dictionaries
│   │   └── DictionaryProviderInterface.php
│   └── Settings
│       └── AppSettingsService.php
├── Entity
├── EventSubscriber
│   └── ApiExceptionSubscriber.php
├── Infrastructure
│   ├── DB
│   │   └── SchemaSqlHelper.php
│   ├── Dictionaries
│   │   ├── ApplicationSettingsDictionary.php
│   │   ├── CountriesDictionary.php
│   │   ├── RolesCountriesDictionary.php
│   │   └── RolesDictionary.php
│   └── EventPublisher
│       ├── EmailEventPublisherInterface.php
│       ├── EmailEventSerializer.php
│       └── HttpEmailEventPublisher.php
├── Kernel.php
├── Repository
├── Shared
│   ├── Cache
│   │   ├── CacheKeyBuilder.php
│   │   └── JsonCacheSerializer.php
│   ├── Exception
│   │   ├── ApiExceptionInterface.php
│   │   ├── BadRequestException.php
│   │   ├── DictionaryNotFoundException.php
│   │   ├── ErrorCode.php
│   │   ├── ExceptionPayload.php
│   │   ├── TooManyRequestsException.php
│   │   └── UnprocessableEntityException.php
│   ├── Http
│   │   ├── Contract
│   │   │   ├── DictionariesData.php
│   │   │   ├── DictionariesErrorData.php
│   │   │   ├── DictionariesErrorResponse.php
│   │   │   ├── DictionariesSuccessResponse.php
│   │   │   └── ResponseContractInterface.php
│   │   └── DictionariesResponseContract.php
│   ├── Time
│   │   ├── ClockInterface.php
│   │   └── SystemClock.php
│   └── Utils
│       └── VerificationCodeGenerator.php
└── str.txt

26 directories, 40 files
```

## <a id="poyasneniya"></a>Пояснения по слоям

### Domain
- Здесь хранятся чистые интерфейсы (`DictionaryProviderInterface`) для работы со справочниками.
- Не содержит зависимостей от Symfony или БД.

### Application
- Сервисы (`DictionaryService`) управляют справочниками и используют провайдеры.
- DTO (`RoleDto`) описывает структуры данных для API.
- Исключения (`DictionaryNotFoundException`) задают бизнес-ошибки и коды для API.

### Infrastructure
- Конкретная реализация провайдеров (`RolesDictionary`), которые извлекают данные из БД.
- Реализует интерфейсы Domain.

### Controller
- Обрабатывает HTTP-запросы и формирует ответы в формате API.
- Контроллеры операций сеттинга (`SettingsController`) отделены от контроллеров справочников (`DictionariesController`).

### EventSubscriber
- Перехватывает все исключения и возвращает стандартизированный JSON с кодами ошибок и timestamp.
- Работает для кастомных исключений (404, 500) и технических ошибок (PDO, DBAL).


## <a id="posledovatelnost"></a>Последовательность сборки
```bash
# Клонируем основной репозиторий в директорию /services/verification-service/app
git clone https://git.tknovosib.ru/omni/dictionaries-service.git .
```
```bash
# После клонирования репозитория собираем Docker-compose
docker compose down -v
docker compose up -d --build     
```
```bash
# как только все контейнеры собраны запускаем миграцию базы данных внутри контейнера с проектом
php bin/console doctrine:migrations:migrate   
```
```bash
# Подключение к DB через любой консольный клиент поддерживающий Postgresql
host: localhost
port: $POSTGRES_USER
username: $POSTGRES_USER   
password: $POSTGRES_PASSWORD
```
```bash
# роутинг идет по префиксам в URL - префикс данного проекта
/api/dictionaries/
```



