<?php

namespace App\Core;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Санитизация HTML контента для защиты от XSS.
 * Использует HTMLPurifier с безопасной конфигурацией.
 */
class Sanitizer
{
    private static ?HTMLPurifier $purifier = null;
    private static ?HTMLPurifier $purifierStrict = null;

    /**
     * Получить стандартный экземпляр очистителя (для контента статей, лендингов).
     * Разрешает базовые HTML теги, блокирует скрипты, события, iframe и т.д.
     */
    public static function get(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        // HTML5 doctype
        $config->set('HTML.Doctype', 'HTML5');

        // Кодировка
        $config->set('Core.Encoding', 'UTF-8');

        // Удаляем неизвестные теги вместо их экранирования
        $config->set('HTML.UnknownElements', 'remove');

        // Разрешаемые теги
        $config->set('HTML.AllowedElements', [
            'a', 'abbr', 'address', 'article', 'aside', 'b', 'bdi', 'bdo', 'blockquote', 'br',
            'caption', 'cite', 'code', 'col', 'colgroup', 'data', 'dd', 'del', 'details', 'dfn',
            'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4',
            'h5', 'h6', 'header', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'main', 'mark', 'nav',
            'ol', 'p', 'pre', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'section', 'small', 'span',
            'strong', 'sub', 'sup', 'summary', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead',
            'time', 'tr', 'u', 'ul', 'var', 'wbr',
        ]);

        // Разрешаемые атрибуты
        $config->set('HTML.AllowedAttributes', [
            'a.href', 'a.title', 'a.rel', 'a.target', 'a.name', 'a.id', 'a.class',
            'abbr.title', 'address.class', 'article.class', 'aside.class',
            'b.class', 'bdi.class', 'bdo.class', 'blockquote.cite', 'blockquote.class',
            'br.class', 'caption.class', 'cite.class', 'code.class', 'col.span', 'col.class',
            'colgroup.span', 'colgroup.class', 'data.value', 'data.class', 'dd.class',
            'del.cite', 'del.datetime', 'del.class', 'details.class', 'details.open',
            'dfn.class', 'div.class', 'dl.class', 'dt.class', 'em.class', 'figcaption.class',
            'figure.class', 'footer.class', 'h1.class', 'h2.class', 'h3.class', 'h4.class',
            'h5.class', 'h6.class', 'header.class', 'hr.class', 'i.class', 'img.src', 'img.alt',
            'img.title', 'img.width', 'img.height', 'img.loading', 'img.decoding', 'img.class',
            'ins.cite', 'ins.datetime', 'ins.class', 'kbd.class', 'li.class', 'main.class',
            'mark.class', 'nav.class', 'ol.class', 'ol.reversed', 'ol.start', 'ol.type', 'p.class',
            'pre.class', 'q.cite', 'q.class', 'rp.class', 'rt.class', 'ruby.class', 's.class',
            'samp.class', 'section.class', 'small.class', 'span.class', 'strong.class',
            'sub.class', 'sup.class', 'summary.class', 'table.class', 'tbody.class', 'td.class',
            'td.colspan', 'td.rowspan', 'tfoot.class', 'th.class', 'th.colspan', 'th.rowspan',
            'th.scope', 'thead.class', 'time.datetime', 'time.class', 'tr.class', 'u.class',
            'ul.class', 'var.class', 'wbr.class',
        ]);

        // URI фильтрация для ссылок и изображений
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('URI.DisableExternalResources', false);

        // Изображения: разрешаем data: URI для base64 (опционально)
        $config->set('URI.SafeIframeRegexp', '%^$'); // запрещаем iframe

        // Автоматически добавляем rel="noopener noreferrer" для target="_blank"
        $config->set('HTML.TargetBlank', 'noopener noreferrer');

        // CSS: разрешаем inline styles, но только безопасные свойства
        $config->set('CSS.AllowedProperties', [
            'color', 'background-color', 'font-size', 'font-weight', 'font-style',
            'text-align', 'text-decoration', 'margin', 'margin-top', 'margin-right',
            'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right',
            'padding-bottom', 'padding-left', 'border', 'border-radius', 'width',
            'height', 'max-width', 'max-height', 'display', 'float', 'clear',
            'line-height', 'letter-spacing', 'font-family', 'opacity', 'text-transform',
            'vertical-align', 'white-space', 'word-wrap', 'overflow', 'box-sizing',
        ]);

        // Кэширование конфигурации
        $config->set('Cache.DefinitionImpl', null);

        self::$purifier = new HTMLPurifier($config);
        return self::$purifier;
    }

    /**
     * Получить строгий экземпляр (для пользовательского ввода: комментарии, описания).
     * Разрешает только минимальный набор тегов.
     */
    public static function getStrict(): HTMLPurifier
    {
        if (self::$purifierStrict !== null) {
            return self::$purifierStrict;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'HTML5');
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.UnknownElements', 'remove');

        // Только базовые inline теги
        $config->set('HTML.AllowedElements', [
            'a', 'abbr', 'b', 'blockquote', 'br', 'cite', 'code', 'del', 'dfn',
            'em', 'i', 'ins', 'kbd', 'mark', 'p', 'pre', 'q', 's', 'samp',
            'small', 'span', 'strong', 'sub', 'sup', 'time', 'u', 'var', 'wbr',
        ]);

        $config->set('HTML.AllowedAttributes', [
            'a.href', 'a.title', 'a.rel', 'a.target', 'a.class',
            'abbr.title', 'blockquote.cite', 'cite.class', 'code.class',
            'del.cite', 'del.datetime', 'ins.cite', 'ins.datetime',
            'q.cite', 'span.class', 'time.datetime',
        ]);

        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('URI.SafeIframeRegexp', '%^$');
        $config->set('HTML.TargetBlank', 'noopener noreferrer');

        self::$purifierStrict = new HTMLPurifier($config);
        return self::$purifierStrict;
    }

    /**
     * Очистить HTML контент (стандартный режим).
     *
     * @param string $html
     * @return string
     */
    public static function clean(string $html): string
    {
        return self::get()->purify($html);
    }

    /**
     * Очистить HTML контент (строгий режим).
     *
     * @param string $html
     * @return string
     */
    public static function cleanStrict(string $html): string
    {
        return self::getStrict()->purify($html);
    }
}