# wp-b24

### Плагин для передачи заказов из WordPress в Битрикс 24 (Разработан для AdaConde)

Добавлена передача данных из контактных форм "Contact-form-7", сделано на базе hooks модуля "Flamingo".

Связь по полям сделана следующим образом

UF_CRM_1569417456=>USER=>billing_new_city
UF_CRM_1569417476=>USER=>billing_country
UF_CRM_1569417591=>1
SOURCE_ID=>1
UF_CRM_1569417476=>Россия
UF_CRM_1569417427=>typem


##### UF_CRM_1569417456=>USER=>billing_new_city
Где UF_CRM_1569417456 - ID поля в Б24
USER - Сущность
billing_new_city - Значение