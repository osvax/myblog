// Подсветка синтаксиса кода в статьях (вставленных через редактор админки).
// Подключается только на страницах статей (флаг $includeArticleScript в лейауте).
import hljs from 'highlight.js/lib/core';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import 'highlight.js/styles/github.css';

// Языки из списка модалки «Вставить код» редактора статей
// (в пакете HTML представлен как xml — регистрируем оба имени)
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('html', xml);
hljs.registerLanguage('css', css);
hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('bash', bash);

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('pre code[class*="language-"]').forEach(function (block) {
        hljs.highlightElement(block);
    });
});
