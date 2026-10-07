<?php

namespace App\Shared\Exception;

/**
 * Единый список всех уникальных кодов ошибок в приложении.
 * Используется в exception классах, например UnprocessableEntityException.
 */
enum ErrorCode: string
{
    // Общие
    case S_INTERNAL_SERVER_ERROR = 'S_INTERNAL_SERVER_ERROR';
    case S_BAD_REQUEST = 'S_BAD_REQUEST';
    case S_DEFAULT_UNPROCESSABLE_ENTITY = 'S_DEFAULT_UNPROCESSABLE_ENTITY';
    case S_NOT_FOUND = 'S_NOT_FOUND';
    case S_ACCESS_DENIED = 'S_ACCESS_DENIED';
    case S_SEND_FAILED = 'S_SEND_FAILED'; // Ошибка при отправке кода
    case S_ROUTE_NOT_FOUND = 'S_ROUTE_NOT_FOUND'; // Маршрут API не найден


    // Быстрая регистрация
    case B_USER_NOT_FOUND = 'USER_NOT_FOUND';
    case B_USER_EMAIL_EXISTS = 'USER_EMAIL_EXISTS';
    case B_EMAIL_VERIFICATION_ATTEMPTS_EXCEEDED = 'B_EMAIL_VERIFICATION_ATTEMPTS_EXCEEDED'; // Попытки проверки кода закончились или истёк TTL
    case B_ROLE_UNAVAILABLE_IN_COUNTRY = 'B_ROLE_UNAVAILABLE_IN_COUNTRY'; // Роль поставщика недоступна в стране. Обратитесь в поддержку
    case B_RESEND_INTERVAL_NOT_EXPIRED = 'B_RESEND_INTERVAL_NOT_EXPIRED'; // Интервал повторной отправки кода ещё не истёк
    case B_MAX_RESEND_ATTEMPTS_EXCEEDED = 'B_MAX_RESEND_ATTEMPTS_EXCEEDED'; // Достигнуто максимальное количество повторных генераций кода
    case B_TOO_MANY_REQUESTS = 'B_TOO_MANY_REQUESTS'; // Слишком много попыток регисрации с эти адресом почты. Прошу ободжать одну минуту.
    case B_MAIL_REGISTRATION_ALREADY_PENDING = 'B_MAIL_REGISTRATION_ALREADY_PENDING'; // Вы уже начали регистрацию  с этим адресом эл почты. Прошу вас ввести код проверки.
    case B_PERSONAL_DATA_CONSENT_REQUIRED ='B_PERSONAL_DATA_CONSENT_REQUIRED'; // Согласие на использование личных данных должно быть получено
    case B_MARKETING_CONSENT_REQUIRED ='B_MARKETING_CONSENT_REQUIRED'; // Подтверждение согласия на маркетинг обязательно
    case B_MARKETING_VERSION_MISSING = 'B_MARKETING_VERSION_MISSING'; // Наличие версии согласия обязательно
    case B_FIELD_REQUIRED = 'B_FIELD_REQUIRED'; // Отсутствует поле в переданных данных
    case B_FIELD_MUST_BE_STRING = 'B_FIELD_MUST_BE_STRING'; // Поле должно быть строкой
    case B_FIELD_MUST_BE_STRING_OR_NULL = 'B_FIELD_MUST_BE_STRING_OR_NULL'; // Поле должно быть строкой или null
    case B_FIELD_MUST_BE_BOOL = 'B_FIELD_MUST_BE_BOOL'; // Поле должно быть логическим
    case B_EMAIL_INVALID = 'B_EMAIL_INVALID'; // Поле email не валидно


    // Справочники
    case B_NOT_FOUND_DICTIONARY_COUNTRIES = 'B_NOT_FOUND_DICTIONARY_COUNTRIES' ; // Справочник стран не найден
    case B_ROLE_NOT_FOUND = 'B_ROLE_NOT_FOUND'; // Роль не найдена в справочнике ролей
    case B_COUNTRY_NOT_FOUND = 'B_COUNTRY_NOT_FOUND'; // Страна не найдена в справочнике стран

    // Валидация
    case B_VALIDATION_FAILED = 'B_VALIDATION_FAILED';
}
