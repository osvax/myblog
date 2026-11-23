(function () {
    'use strict';

    var storageKey = 'myblog.site.settings.v1';
    var defaults = {
        contactAddress: 'г. Самара, ул. Примерная, 1',
        contactEmail: 'hello@moyblog.ru',
        contactPhone: '+7 (900) 123-45-67',
        contactHours: 'Пн–Пт, 10:00–18:00',
        contactAddressEnabled: true,
        contactAddressLabel: 'Адрес',
        contactHoursEnabled: true,
        contactHoursLabel: 'Часы работы',
        contactEmailEnabled: true,
        contactEmailLabel: 'Email',
        contactPhoneEnabled: true,
        contactPhoneLabel: 'Телефон',
        socialTelegramEnabled: true,
        socialTelegramLabel: 'Telegram',
        socialVkEnabled: true,
        socialVkLabel: 'VK',
        socialGithubEnabled: true,
        socialGithubLabel: 'GitHub',
        socialYoutubeEnabled: true,
        socialYoutubeLabel: 'YouTube',
        socialTelegram: '',
        socialVk: '',
        socialGithub: '',
        socialYoutube: '',
        consultantEnabled: true,
        consultantWorkingHours: '09:00-20:00',
        consultantButtonColor: '#ff8c1a',
        consultantButtonTextColor: '#ffffff',
        consultantButtonPosition: 'right',
        consultantCensoredWords: 'дурак, идиот, тупица',
        consultantWelcomeMessage: 'Здравствуйте! Чем могу помочь?',
        consultantNotificationEmail: ''
    };

    function load() {
        try {
            var stored = localStorage.getItem(storageKey);
            return stored ? Object.assign({}, defaults, JSON.parse(stored)) : Object.assign({}, defaults);
        } catch (error) {
            console.error('Не удалось загрузить настройки сайта.', error);
            return Object.assign({}, defaults);
        }
    }

    function save(settings) {
        localStorage.setItem(storageKey, JSON.stringify(settings));
    }

    function applyPublic() {
        var settings = load();
        document.querySelectorAll('[data-site-setting]').forEach(function (element) {
            var value = String(settings[element.dataset.siteSetting] || '').trim();
            element.textContent = value;
            var card = element.closest('.col-md-4');
            if (card) card.hidden = !value;
        });
        document.querySelectorAll('[data-site-social]').forEach(function (link) {
            var value = String(settings['social' + link.dataset.siteSocial] || '').trim();
            try {
                var url = new URL(value);
                if (url.protocol !== 'https:' && url.protocol !== 'http:') throw new Error('Unsupported URL protocol');
                link.href = url.href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.hidden = false;
            } catch (error) {
                link.hidden = true;
                link.removeAttribute('href');
            }
        });
    }

    window.SiteSettings = { load: load, save: save, applyPublic: applyPublic };

    if (document.querySelector('[data-site-setting], [data-site-social]')) {
        document.addEventListener('DOMContentLoaded', applyPublic);
    }
}());
