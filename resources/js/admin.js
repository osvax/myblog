// ============================================================
// Административная панель «МойБлог» — Vite entry
// Порт public/admin/js/admin.js + public/admin/js/admin-api.js
// CSS — resources/scss/admin.scss (порт admin.css + темы сайта)
//
// Глобальные объекты для page-скриптов (/admin/js/*.js):
//   window.AdminApi, window.AdminNotifications
// Bootstrap (window.bootstrap) загружается в лейауте CDN-бандлом
// до выполнения модуля — page-скрипты обращаются к нему на top-level.
// ============================================================

import '../scss/admin.scss';
import { AdminApi } from './admin-api.js';

window.AdminApi = AdminApi;

// ---------- Контейнер для уведомлений ----------
const toastContainer = document.createElement('div');
toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
toastContainer.style.zIndex = '3000';
document.body.appendChild(toastContainer);

function showToast(type, text) {
    const tones = {
        success: { className: 'text-bg-success', icon: 'bi-check-circle-fill' },
        error: { className: 'text-bg-danger', icon: 'bi-x-circle-fill' },
        warning: { className: 'text-bg-warning', icon: 'bi-exclamation-triangle-fill' },
        info: { className: 'text-bg-primary', icon: 'bi-info-circle-fill' },
        secondary: { className: 'text-bg-secondary', icon: 'bi-bell-fill' }
    };
    if (typeof type === 'boolean') type = type ? 'success' : 'error';
    const tone = tones[type] || tones.info;
    const el = document.createElement('div');
    el.className = 'toast align-items-center border-0 ' + tone.className;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    el.setAttribute('aria-atomic', 'true');
    const content = document.createElement('div');
    content.className = 'd-flex';
    const body = document.createElement('div');
    body.className = 'toast-body fw-bold';
    const icon = document.createElement('i');
    icon.className = 'bi ' + tone.icon + ' me-2';
    body.append(icon, document.createTextNode(text || 'Операция выполнена.'));
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close' + (type === 'warning' ? '' : ' btn-close-white') + ' me-2 m-auto';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', 'Закрыть');
    content.append(body, close);
    el.appendChild(content);
    toastContainer.appendChild(el);
    const toast = new bootstrap.Toast(el, { delay: 3500 });
    toast.show();
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
}
window.AdminNotifications = { show: showToast };

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Фильтр по пунктам меню сайдбара ----------
    const navFilter = document.getElementById('navFilter');
    const adminNav = document.getElementById('adminNav');
    if (navFilter && adminNav) {
        navFilter.addEventListener('input', function () {
            const query = navFilter.value.trim().toLowerCase();
            adminNav.querySelectorAll('.admin-link').forEach(function (link) {
                const text = (link.textContent || '').toLowerCase();
                const wrap = link.closest('.collapse') || link;
                link.classList.toggle('d-none', query !== '' && !text.includes(query));
                if (wrap.classList.contains('collapse') && query !== '') {
                    wrap.classList.add('show');
                    wrap.previousElementSibling.setAttribute('aria-expanded', 'true');
                }
            });
            adminNav.querySelectorAll('.admin-nav-group-btn').forEach(function (button) {
                const panel = adminNav.querySelector(button.dataset.bsTarget);
                const hasVisible = panel && panel.querySelectorAll('.admin-link:not(.d-none)').length > 0;
                button.classList.toggle('d-none', query !== '' && !hasVisible);
            });
        });
    }

    // ---------- Кнопка «Наверх» ----------
    const backToTop = document.createElement('button');
    backToTop.type = 'button';
    backToTop.className = 'btn btn-orange btn-action admin-back-to-top';
    backToTop.setAttribute('aria-label', 'Наверх');
    backToTop.title = 'Наверх';
    backToTop.innerHTML = '<i class="bi bi-arrow-up fs-5"></i>';
    backToTop.hidden = true;
    document.body.appendChild(backToTop);
    backToTop.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    window.addEventListener('scroll', function () {
        backToTop.hidden = window.scrollY < 400;
    }, { passive: true });

    // ---------- Тултипы на кнопках с подсказками ----------
    document.querySelectorAll('.admin-topbar [title], .admin-nav [title]').forEach(function (element) {
        if (bootstrap.Tooltip) {
            new bootstrap.Tooltip(element);
        }
    });

    // ---------- Выход из системы ----------
    document.querySelectorAll('form[action="/api/auth/logout"]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            fetch('/api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
                if (!response.ok) throw new Error('Не удалось проверить права администратора.');
                return response.json();
            }).then(function (csrf) {
                if (!csrf.success || !csrf.token) throw new Error('Не удалось проверить права администратора.');
                return fetch('/api/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-Token': csrf.token }
                });
            }).then(function (response) {
                if (!response.ok) throw new Error('Не удалось завершить сеанс.');
                window.location.href = 'login.html';
            }).catch(function (error) {
                if (window.AdminNotifications) {
                    window.AdminNotifications.show(false, error.message || 'Не удалось выйти из системы.');
                } else {
                    console.error(error);
                }
            });
        });
    });

    // ---------- Индикатор новых заказов ----------
    const newOrderLinks = Array.from(document.querySelectorAll('.admin-link[href$="/new-orders.html"]'));
    if (newOrderLinks.length) {
        let orderRecords = null;

        function renderNewOrderCount() {
            if (!orderRecords) return;
            const count = orderRecords.filter(function (order) {
                return order.status === 'Новый';
            }).length;

            newOrderLinks.forEach(function (link) {
                let bell = link.querySelector('.new-orders-bell');
                if (!bell) {
                    const icon = link.querySelector('i');
                    if (!icon) return;
                    bell = document.createElement('span');
                    bell.className = 'new-orders-bell';
                    icon.classList.add('new-orders-bell-primary');
                    icon.replaceWith(bell);
                    bell.appendChild(icon);
                    const secondBell = document.createElement('i');
                    secondBell.className = 'bi bi-bell-fill new-orders-bell-secondary';
                    secondBell.setAttribute('aria-hidden', 'true');
                    bell.appendChild(secondBell);
                }

                const badge = link.querySelector('.new-orders-count');
                if (badge) badge.remove();
                link.classList.toggle('has-new-orders', count > 0);
                const primaryBell = bell.querySelector('.new-orders-bell-primary');
                if (primaryBell) {
                    primaryBell.classList.toggle('bi-bell', count === 0);
                    primaryBell.classList.toggle('bi-bell-fill', count > 0);
                }
                link.setAttribute('aria-label', count > 0 ? 'Новые заказы: ' + count : 'Новые');
                if (count > 0) {
                    const countBadge = document.createElement('span');
                    countBadge.className = 'new-orders-count';
                    countBadge.textContent = count > 99 ? '99+' : String(count);
                    countBadge.setAttribute('aria-hidden', 'true');
                    link.appendChild(countBadge);
                }
            });
        }

        function loadNewOrderCount() {
            window.AdminApi.get('/api/admin/orders').then(function (data) {
                orderRecords = data.orders;
                renderNewOrderCount();
            }).catch(function (error) {
                console.error('Не удалось обновить счётчик новых заказов.', error);
            });
        }

        loadNewOrderCount();
        window.addEventListener('admin-orders-state-changed', loadNewOrderCount);
    }

    // ---------- Уведомления о новых сообщениях консультанта ----------
    const consultantLinks = Array.from(document.querySelectorAll('.admin-link[href="consultant.html"]'));
    if (consultantLinks.length) {
        const consultantReadStateKey = 'myblog.admin.consultant.read.v1';
        let previousUnreadMessageKeys = null;
        let notificationAudio = null;
        let audioUnlocked = false;
        const originalDocumentTitle = document.title;
        let titleAttentionTimer = null;
        let titleAttentionVisible = false;
        let notificationAudioPending = false;

        function stopTitleAttention() {
            if (titleAttentionTimer !== null) {
                window.clearInterval(titleAttentionTimer);
                titleAttentionTimer = null;
            }
            document.title = originalDocumentTitle;
            titleAttentionVisible = false;
        }

        function startTitleAttention() {
            if (titleAttentionTimer !== null) return;
            titleAttentionTimer = window.setInterval(function () {
                titleAttentionVisible = !titleAttentionVisible;
                document.title = titleAttentionVisible
                    ? '🔔 Новое сообщение'
                    : originalDocumentTitle;
            }, 900);
        }

        window.addEventListener('focus', stopTitleAttention);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) stopTitleAttention();
        });

        function getConsultantReadState() {
            try {
                const state = JSON.parse(localStorage.getItem(consultantReadStateKey) || '{}');
                return state && typeof state === 'object' && !Array.isArray(state) ? state : {};
            } catch (error) {
                console.warn('Не удалось прочитать состояние прочитанных обращений.', error);
                return {};
            }
        }

        function unlockNotificationAudio() {
            if (audioUnlocked) return;
            audioUnlocked = true;
            const AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextConstructor) return;
            try {
                notificationAudio = new AudioContextConstructor();
                if (notificationAudio.state === 'suspended') {
                    notificationAudio.resume().then(function () {
                        if (notificationAudioPending) {
                            notificationAudioPending = false;
                            playConsultantNotification();
                        }
                    }).catch(function (error) {
                        console.warn('Не удалось включить звук уведомлений.', error);
                    });
                } else if (notificationAudioPending) {
                    notificationAudioPending = false;
                    playConsultantNotification();
                }
            } catch (error) {
                console.warn('Не удалось включить звук уведомлений.', error);
            }
        }

        document.addEventListener('pointerdown', unlockNotificationAudio);
        document.addEventListener('keydown', unlockNotificationAudio);

        function playConsultantNotification() {
            if (!notificationAudio || notificationAudio.state === 'suspended') {
                notificationAudioPending = true;
                if (notificationAudio && notificationAudio.state === 'suspended') {
                    notificationAudio.resume().then(function () {
                        if (notificationAudioPending) {
                            notificationAudioPending = false;
                            playConsultantNotification();
                        }
                    }).catch(function (error) {
                        console.warn('Не удалось включить звук уведомлений.', error);
                    });
                }
                return;
            }
            const playTone = function (frequency, startAt) {
                const oscillator = notificationAudio.createOscillator();
                const gain = notificationAudio.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, startAt);
                gain.gain.setValueAtTime(0.0001, startAt);
                gain.gain.exponentialRampToValueAtTime(0.16, startAt + 0.025);
                gain.gain.exponentialRampToValueAtTime(0.0001, startAt + 0.22);
                oscillator.connect(gain);
                gain.connect(notificationAudio.destination);
                oscillator.start(startAt);
                oscillator.stop(startAt + 0.23);
            };

            const play = function () {
                const startAt = notificationAudio.currentTime + 0.02;
                playTone(740, startAt);
                playTone(990, startAt + 0.16);
            };

            if (notificationAudio.state === 'suspended') {
                notificationAudio.resume().then(play).catch(function (error) {
                    console.warn('Не удалось воспроизвести звук уведомления.', error);
                });
            } else {
                play();
            }
        }

        function renderConsultantCount(users) {
            const newUsers = users.filter(function (user) {
                return user.status === 'new';
            });
            const readState = getConsultantReadState();
            const unreadUsers = newUsers.filter(function (user) {
                return user.lastMessageRole === 'user' &&
                    String(user.lastUserMessageId || user.lastMessageId || '') !== String(readState[user.id] || '');
            });
            const unreadMessageKeys = new Set(unreadUsers.map(function (user) {
                return String(user.id) + ':' + String(user.lastUserMessageId || user.lastMessageId || '');
            }));

            if (previousUnreadMessageKeys !== null) {
                const hasNewMessage = Array.from(unreadMessageKeys).some(function (key) {
                    return !previousUnreadMessageKeys.has(key);
                });
                if (hasNewMessage) {
                    playConsultantNotification();
                    startTitleAttention();
                }
            }
            previousUnreadMessageKeys = unreadMessageKeys;

            consultantLinks.forEach(function (link) {
                let badge = link.querySelector('.consultant-unread-count');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'consultant-unread-count';
                    badge.setAttribute('aria-hidden', 'true');
                    link.appendChild(badge);
                }

                const count = unreadUsers.length;
                badge.textContent = count > 99 ? '99+' : String(count);
                badge.hidden = count === 0;
                link.classList.toggle('has-consultant-requests', count > 0);
                link.setAttribute('aria-label', count > 0 ? 'Консультант: ' + count + ' непрочитанных сообщений' : 'Консультант');
            });
        }

        function loadConsultantCount() {
            fetch('../api/consultant', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json; charset=utf-8' },
                body: JSON.stringify({ action: 'getState' })
            }).then(function (response) {
                if (response.status === 401) return null;
                return response.json().then(function (data) {
                    if (!response.ok || !data.success || !data.data || !Array.isArray(data.data.users)) {
                        throw new Error(data.message || 'Некорректный ответ списка обращений.');
                    }
                    return data.data.users;
                });
            }).then(function (users) {
                if (users) renderConsultantCount(users);
            }).catch(function (error) {
                console.error('Не удалось обновить счётчик консультанта.', error);
            });
        }

        loadConsultantCount();
        window.setInterval(loadConsultantCount, 10000);
        window.addEventListener('admin-consultant-read', loadConsultantCount);
        window.addEventListener('storage', function (event) {
            if (event.key === consultantReadStateKey) loadConsultantCount();
        });
    }

    // ---------- Подтверждение удаления ----------
    const confirmation = document.createElement('div');
    confirmation.className = 'modal fade';
    confirmation.tabIndex = -1;
    confirmation.setAttribute('aria-hidden', 'true');
    confirmation.innerHTML =
        '<div class="modal-dialog modal-dialog-centered"><div class="modal-content">' +
        '<div class="modal-header"><h2 class="modal-title fs-5">Подтверждение удаления</h2>' +
        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button></div>' +
        '<div class="modal-body"><p class="mb-0">Вы уверены?</p></div>' +
        '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-confirm-no>Нет</button>' +
        '<button type="button" class="btn btn-danger" data-confirm-yes>Да</button></div></div></div>';
    document.body.appendChild(confirmation);
    const confirmationModal = bootstrap.Modal.getOrCreateInstance(confirmation);
    const confirmedDeletes = new WeakSet();

    function askDeleteConfirmation(button) {
        const question = confirmation.querySelector('.modal-body p');
        const label = (button.getAttribute('aria-label') || button.getAttribute('title') || button.textContent || '')
            .replace(/^\s*Удалить\s*/i, '').trim();
        question.textContent = label ? 'Вы уверены? Удалить «' + label + '»?' : 'Вы уверены?';
        return new Promise(function (resolve) {
            let answer = false;
            function finish() {
                confirmation.removeEventListener('hidden.bs.modal', onHidden);
                resolve(answer);
            }
            function onHidden() {
                finish();
            }
            confirmation.addEventListener('hidden.bs.modal', onHidden, { once: true });
            confirmation.querySelector('[data-confirm-no]').onclick = function () {
                answer = false;
                confirmationModal.hide();
            };
            confirmation.querySelector('[data-confirm-yes]').onclick = function () {
                answer = true;
                confirmationModal.hide();
            };
            confirmationModal.show();
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest(
            '[data-delete], [data-delete-file], [data-delete-type], [data-delete-feature], [data-order-delete], [data-article-delete], [data-portfolio-delete], .feature-delete, ' +
            '[aria-label^="Удалить"], [title^="Удалить"]'
        );
        if (!button) return;
        if (confirmedDeletes.has(button)) {
            confirmedDeletes.delete(button);
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        askDeleteConfirmation(button).then(function (confirmed) {
            if (!confirmed || !button.isConnected) return;
            confirmedDeletes.add(button);
            button.click();
        });
    }, true);

    // ---------- Переключение сайдбара (мобильные) ----------
    const sidebar = document.getElementById('adminSidebar');
    const burger = document.getElementById('sidebarToggle');
    const sidebarCollapseToggle = document.getElementById('sidebarCollapseToggle');
    const adminWrapper = document.querySelector('.admin-wrapper');
    if (adminWrapper && sidebarCollapseToggle) {
        try {
            adminWrapper.classList.toggle('sidebar-collapsed', localStorage.getItem('myblog.admin.sidebar-collapsed') === 'true');
        } catch (error) {
            console.error('Не удалось загрузить настройку боковой панели.', error);
        }

        const collapsed = adminWrapper.classList.contains('sidebar-collapsed');
        sidebarCollapseToggle.setAttribute('aria-pressed', String(collapsed));
        sidebarCollapseToggle.setAttribute('aria-label', collapsed ? 'Развернуть боковую панель' : 'Свернуть боковую панель');
        sidebarCollapseToggle.title = collapsed ? 'Развернуть меню' : 'Свернуть меню';
        sidebarCollapseToggle.innerHTML = '<i class="bi ' + (collapsed ? 'bi-chevron-bar-right' : 'bi-chevron-bar-left') + '"></i>';

        sidebarCollapseToggle.addEventListener('click', function () {
            const isCollapsed = adminWrapper.classList.toggle('sidebar-collapsed');
            sidebarCollapseToggle.setAttribute('aria-pressed', String(isCollapsed));
            sidebarCollapseToggle.setAttribute('aria-label', isCollapsed ? 'Развернуть боковую панель' : 'Свернуть боковую панель');
            sidebarCollapseToggle.title = isCollapsed ? 'Развернуть меню' : 'Свернуть меню';
            sidebarCollapseToggle.innerHTML = '<i class="bi ' + (isCollapsed ? 'bi-chevron-bar-right' : 'bi-chevron-bar-left') + '"></i>';
            try {
                localStorage.setItem('myblog.admin.sidebar-collapsed', String(isCollapsed));
            } catch (error) {
                console.error('Не удалось сохранить настройку боковой панели.', error);
                showToast(false, 'Не удалось сохранить состояние меню');
            }
        });

        document.querySelectorAll('.admin-link').forEach(function (link) {
            if (!link.hasAttribute('title')) link.title = link.textContent.trim();
        });
    }
    if (sidebar && burger) {
        burger.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 992 &&
                sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) && e.target !== burger) {
                sidebar.classList.remove('open');
            }
        });
    }

    // ---------- Кнопки удаления ----------
    document.querySelectorAll('[data-delete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = btn.closest('tr') || btn.closest('.col-md-6') || btn.closest('.col-lg-4');
            showToast('warning', (btn.dataset.delete || 'Запись удалена') +
                ' только из текущего интерфейса; серверное удаление не настроено.');
            if (row) { row.style.transition = 'opacity .4s'; row.style.opacity = '0'; }
            setTimeout(function () { if (row) row.remove(); }, 400);
        });
    });

    // ---------- Автогенерация slug из названия (кириллица → латиница) ----------
    function transliterate(text) {
        const map = {
            'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'yo','ж':'zh',
            'з':'z','и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o',
            'п':'p','р':'r','с':'s','т':'t','у':'u','ф':'f','х':'kh','ц':'cz',
            'ч':'ch','ш':'sh','щ':'shch','ъ':'','ы':'y','ь':'','э':'e','ю':'yu',
            'я':'ya',
            'А':'A','Б':'B','В':'V','Г':'G','Д':'D','Е':'E','Ё':'Yo','Ж':'Zh',
            'З':'Z','И':'I','Й':'Y','К':'K','Л':'L','М':'M','Н':'N','О':'O',
            'П':'P','Р':'R','С':'S','Т':'T','У':'U','Ф':'F','Х':'Kh','Ц':'Cz',
            'Ч':'Ch','Ш':'Sh','Щ':'Shch','Ъ':'','Ы':'Y','Ь':'','Э':'E','Ю':'Yu',
            'Я':'Ya'
        };
        let result = '';
        for (let i = 0; i < text.length; i++) {
            result += map[text[i]] !== undefined ? map[text[i]] : text[i];
        }
        return result;
    }

    function generateSlug(name) {
        return transliterate(name)
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')   // удаляем всё кроме букв, цифр, пробелов и дефиса
            .trim()
            .replace(/\s+/g, '-')            // пробелы → дефис
            .replace(/-+/g, '-')             // множественные дефисы → один
            .replace(/^-+|-+$/g, '');        // убираем дефисы в начале/конце
    }

    const categoryModal = document.getElementById('categoryModal');
    if (categoryModal) {
        const nameInput = categoryModal.querySelector('input[type="text"]:nth-of-type(1)');
        const slugInput   = categoryModal.querySelectorAll('input[type="text"]')[1];
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (val) {
                    slugInput.value = generateSlug(val);
                } else {
                    slugInput.value = '';
                }
            });
        }
    }

    // ---------- Формы создания/обновления ----------
    document.querySelectorAll('.admin-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                showToast('error', 'Заполните обязательные поля формы');
                return;
            }
            showToast('error', 'Не удалось сохранить данные: эта форма пока не подключена к серверу.');
        });
    });

    // ---------- Смена роли / статуса в таблицах ----------
    document.querySelectorAll('.role-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            showToast('warning', 'Изменение прав пока действует только в интерфейсе');
        });
    });

    document.querySelectorAll('.status-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            showToast('info', 'Статус изменён только в интерфейсе; серверное сохранение не настроено');
        });
    });

    // ---------- Заполнение модального окна ответа ----------
    document.querySelectorAll('.reply-modal-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('replyToName').value = this.dataset.name || '';
            document.getElementById('replyToEmail').value = this.dataset.email || '';
            document.getElementById('replyOriginalMessage').value = this.dataset.message || '';
        });
    });

    // ---------- Синхронизация пикеров цветов с hex-полями ----------
    const colorPairs = [
        { picker: 'colorPrimary', hex: 'colorPrimaryHex' },
        { picker: 'colorDark', hex: 'colorDarkHex' },
        { picker: 'colorLight', hex: 'colorLightHex' },
        { picker: 'colorText', hex: 'colorTextHex' },
        { picker: 'colorLink', hex: 'colorLinkHex' },
        { picker: 'colorBg', hex: 'colorBgHex' },
        { picker: 'colorBtn', hex: 'colorBtnHex' },
        { picker: 'colorBadge', hex: 'colorBadgeHex' }
    ];
    colorPairs.forEach(function (pair) {
        const picker = document.getElementById(pair.picker);
        const hex = document.getElementById(pair.hex);
        if (picker && hex) {
            picker.addEventListener('input', function () { hex.value = this.value; });
            hex.addEventListener('input', function () {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) picker.value = this.value;
            });
        }
    });

    // ---------- Авто-расчёт ширины карточки при смене количества колонок ----------
    const blogColumns = document.getElementById('blogColumns');
    const blogCardWidth = document.getElementById('blogCardWidth');
    if (blogColumns && blogCardWidth) {
        const widthMap = { '1': 960, '2': 480, '3': 320, '4': 230 };
        blogColumns.addEventListener('change', function () {
            blogCardWidth.value = widthMap[this.value] || blogCardWidth.value;
        });
    }

    // ---------- Добавление характеристик в калькулятор заказа ----------
    const addFeatureBtn = document.getElementById('addFeatureBtn');
    const addFeatureModal = document.getElementById('addFeatureModal');
    if (addFeatureModal && addFeatureBtn) {
        const featureNameInput = document.getElementById('newFeatureName');
        const featurePriceInput = document.getElementById('newFeaturePrice');
        const featureDaysInput = document.getElementById('newFeatureDays');
        const featureProjectInput = document.getElementById('newFeatureProject');

        // Заполнение project при открытии модала
        document.querySelectorAll('[data-bs-target="#addFeatureModal"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                featureProjectInput.value = this.dataset.project || '';
                featureNameInput.value = '';
                featurePriceInput.value = '';
                featureDaysInput.value = '';
            });
        });

        // Добавление новой характеристики
        addFeatureBtn.addEventListener('click', function () {
            const project = featureProjectInput.value;
            const name = featureNameInput.value.trim();
            const price = featurePriceInput.value.trim();
            const days = featureDaysInput.value.trim();

            if (!name || !price || !days) {
                showToast('error', 'Заполните все поля');
                return;
            }

            const container = document.getElementById('features-' + project);
            if (!container) {
                showToast('error', 'Не удалось найти список характеристик');
                return;
            }

            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2';
            row.innerHTML =
                '<div class="col-md-6"><input type="text" class="form-control form-control-sm fw-bold" value="' + name + '"></div>' +
                '<div class="col-md-4"><input type="number" class="form-control form-control-sm" value="' + price + '" placeholder="Цена ₽"></div>' +
                '<div class="col-md-2"><input type="number" class="form-control form-control-sm" value="' + days + '" placeholder="Дни"></div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btn-action feature-delete"><i class="bi bi-trash"></i></button></div>';

            container.appendChild(row);

            // Закрытие модала
            bootstrap.Modal.getInstance(addFeatureModal).hide();
            showToast('info', 'Характеристика добавлена в форму');
        });
    }

    // ---------- Удаление характеристики (делегирование событий) ----------
    document.addEventListener('click', function (e) {
        if (e.target.closest('.feature-delete')) {
            const row = e.target.closest('.row');
            if (row) {
                row.remove();
                showToast('warning', 'Элемент удалён только из формы и ещё не сохранён');
            }
        }
    });

    // ---------- Формы ответов на сообщения ----------
    document.querySelectorAll('.reply-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                showToast('error', 'Напишите ответ');
                return;
            }
            showToast('error', 'Ответ не отправлен: отправка сообщений из админ-панели пока не настроена.');
        });
    });
});
