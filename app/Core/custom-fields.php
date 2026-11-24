<?php

declare(strict_types=1);

/**
 * Публичный хелпер для пользовательских полей (раздел «Пользовательские
 * поля» в админке). Возвращает значение активного поля или значение
 * по умолчанию, если поле не найдено или не заполнено.
 *
 * Пример в шаблоне: <?= custom_field('banner_title', 'Заголовок по умолчанию') ?>
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function custom_field(string $key, $default = '')
{
    static $model = null;
    if ($model === null) {
        $model = new \App\Models\CustomField();
    }
    return $model->value($key, $default);
}
