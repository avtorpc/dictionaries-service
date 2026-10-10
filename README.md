# Место — dictionaries-service

Symfony-сервис общих справочников и настроек. Локальная схема PostgreSQL — dictionaries; данные доступны через общий gateway_nginx по /api/refbook/.

## Рабочие таблицы

Сохранены application_settings (8 настроек регистрации) и countries (3 страны). roles содержит только applicant/Соискатель и employer/Работодатель.

Новые справочники из прототипа __HH__:

| Таблица | API | Записей начальной загрузки |
|---|---|---:|
| specializations | /api/refbook/specializations | 120 |
| grades | /api/refbook/grades | 3 |
| competencies | /api/refbook/competencies | 65 |
| technologies | /api/refbook/technologies | 62 |
| specialization_competencies | /api/refbook/specialization-competencies | 1543 |
| criteria | /api/refbook/criteria | 72 |
| criterion_competencies | /api/refbook/criterion-competencies | 78 |

Список провайдеров — GET /api/refbook/list. Старые роли SELLER/BUYER/DELIVERER заменены. Удалены roles_countries, communication_channel_country_links, communication_channels, positions, verification_statuses, legal_forms, document_types и соответствующие провайдеры/DTO. Их API возвращает 404.

## Миграции

Version20261007190000 удаляет семь устаревших таблиц (дочерние раньше родительских, без CASCADE) и обновляет роли существующей базы. Version20261007190100 создаёт и заполняет семь справочников. Шесть старых файлов миграций создания удалённых таблиц убраны; начальная миграция roles создаёт две актуальные роли. Чистая установка больше не создаёт удалённые таблицы.

Неизменяемые данные миграции находятся в resources/migrations/20261007. Источник — __HH__/data/assessment/sql/01_schema.sql и 02_seed.sql. Перенесены только справочники; tests/test_attempts и демонстрационные результаты не импортируются. JSON-ресурсы содержат отдельные SQL-команды для подготовленных запросов Doctrine. У criteria уникальны code/version и одна активная версия code. Связи защищены внешними ключами; API only_active также учитывает активность родительских записей.

Применение к локальной базе:

```sh
docker exec dictionaries_service_php php bin/console doctrine:migrations:migrate --no-interaction
```

Из ONMI_infra команда make registration-migrate применяет миграции всех сервисов. Удаление старых файлов не стирает выполненную историю public.doctrine_migration_versions_dictionaries: Doctrine может показывать предупреждение о шести ранее выполненных недоступных миграциях. Оно ожидаемо и не мешает применению новых миграций. История сохранена для аудита; повтор migrate не меняет уже созданные справочники. Новые миграции необратимы через down: данные восстанавливаются из резервной копии.

Перед обновлением существующей локальной базы сохранена копия схемы: ONMI_infra/storage/backups/dictionaries-before-mvp-20261007.sql (игнорируется Git). Настройки регистрации и страны сохранены без изменения. Прочие схемы не очищались.

## Проверки

```sh
docker exec dictionaries_service_php php tests/dictionary-migrations-contract.php
docker exec dictionaries_service_php php tests/doctrine-install-contract.php
python3 services/dictionaries-service/tests/dictionary-api-contract.py
python3 services/web-service/tests/registration-e2e.py
```

Первые два теста создают временные схемы/базу и удаляют их в finally. Проверяются чистая установка, заполненная прежняя структура, реальные команды Doctrine и повторный запуск, сохранность настроек/стран, количество записей, FK, уникальность активной версии критерия, фильтрация и сериализация кеша. API/регистрация требуют работающего make registration-up. Python-команды запускаются из корня NEW_HA_HA.

Квалификационные задачи и попытки будут храниться в catalog-service; доступ к справочникам осуществляется через API. Актуальный сценарий регистрации: [registration-mvp.md](../../docs/flows/registration-mvp.md).

## Зависимости без сетевой установки

Полная папка vendor хранится в Git, включая автозагрузчик и лицензии. При запуске контейнера `php bin/check-vendor.php` локально проверяет пакеты по composer.lock и автозагрузку Symfony/Doctrine. Composer и Packagist при запуске не вызываются; неполный или несогласованный vendor останавливает запуск с сообщением об ошибке.

Обновляйте зависимости на машине с доступом к репозиториям через `composer install --no-interaction --prefer-dist --no-scripts`, затем проверяйте `php bin/check-vendor.php`. Сохраняйте весь vendor вместе с composer.json и composer.lock. Для целевого изменения версий используйте composer update. Файлы .env, var и секреты остаются исключены из Git.
