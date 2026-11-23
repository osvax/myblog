<div class="card admin-card">
                <div class="card-header bg-white">
                    <h2 class="h5 fw-bold mb-3"><i class="bi bi-gear text-orange"></i> Настройки сайта</h2>
                    <ul class="nav nav-tabs nav-tabs-settings card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab" aria-controls="general-pane" aria-selected="true" title="Основные"><i class="bi bi-sliders"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-pane" type="button" role="tab" aria-controls="home-pane" aria-selected="false" title="Главная страница"><i class="bi bi-house-door"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mail-tab" data-bs-toggle="tab" data-bs-target="#mail-pane" type="button" role="tab" aria-controls="mail-pane" aria-selected="false" title="Email письма"><i class="bi bi-envelope-paper"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="headings-tab" data-bs-toggle="tab" data-bs-target="#headings-pane" type="button" role="tab" aria-controls="headings-pane" aria-selected="false" title="Заголовки"><i class="bi bi-type-h1"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications-pane" type="button" role="tab" aria-controls="notifications-pane" aria-selected="false" title="Уведомления"><i class="bi bi-bell"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="consultant-tab" data-bs-toggle="tab" data-bs-target="#consultant-pane" type="button" role="tab" aria-controls="consultant-pane" aria-selected="false" title="Консультант"><i class="bi bi-headset"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts-pane" type="button" role="tab" aria-controls="contacts-pane" aria-selected="false" title="Контакты"><i class="bi bi-telephone"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contacts-about-tab" data-bs-toggle="tab" data-bs-target="#contacts-about-pane" type="button" role="tab" aria-controls="contacts-about-pane" aria-selected="false" title="Обо мне"><i class="bi bi-person-vcard"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab" aria-controls="security-pane" aria-selected="false" title="Безопасность"><i class="bi bi-shield-lock"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="authentication-tab" data-bs-toggle="tab" data-bs-target="#authentication-pane" type="button" role="tab" aria-controls="authentication-pane" aria-selected="false" title="Аутентификация"><i class="bi bi-person-badge"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab" aria-controls="logs-pane" aria-selected="false" title="Логи"><i class="bi bi-clock-history"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button" role="tab" aria-controls="email-pane" aria-selected="false" title="Email"><i class="bi bi-envelope-at"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="articles-tab" data-bs-toggle="tab" data-bs-target="#articles-pane" type="button" role="tab" aria-controls="articles-pane" aria-selected="false" title="Статьи"><i class="bi bi-journal-text"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="portfolio-tab" data-bs-toggle="tab" data-bs-target="#portfolio-pane" type="button" role="tab" aria-controls="portfolio-pane" aria-selected="false" title="Портфолио"><i class="bi bi-collection"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance-pane" type="button" role="tab" aria-controls="appearance-pane" aria-selected="false" title="Оформление"><i class="bi bi-palette"></i></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates-pane" type="button" role="tab" aria-controls="templates-pane" aria-selected="false" title="Шаблоны"><i class="bi bi-layout-text-window-reverse"></i></button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <form class="settings-form" id="siteSettingsForm">
                    <div class="tab-content" id="settingsTabsContent">
                        <section class="tab-pane fade show active" id="general-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Основные настройки</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="siteName" class="form-label fw-bold">Название сайта</label>
                                    <input type="text" id="siteName" class="form-control" value="МойБлог">
                                </div>
                                <div class="col-md-6">
                                    <label for="siteTitle" class="form-label fw-bold">Заголовок вкладки (SEO)</label>
                                    <input type="text" id="siteTitle" class="form-control" value="МойБлог — Портфолио и блог">
                                </div>
                                <div class="col-12">
                                    <label for="siteDescription" class="form-label fw-bold">Описание сайта (SEO description)</label>
                                    <textarea id="siteDescription" class="form-control" rows="3">Портфолио веб-разработчика. Блог о верстке, Bootstrap 5 и современных технологиях.</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="adminEmail" class="form-label fw-bold">Email администратора</label>
                                    <input type="email" id="adminEmail" class="form-control" value="admin@moyblog.ru">
                                </div>
                                <div class="col-md-6">
                                    <label for="siteLanguage" class="form-label fw-bold">Язык сайта</label>
                                    <select id="siteLanguage" class="form-select">
                                        <option selected>Русский</option>
                                        <option>English</option>
                                    </select>
                                </div>
                            </div>

                            <h3 class="h5 fw-bold mt-5 mb-3">SEO</h3>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="keywords" class="form-label fw-bold">Ключевые слова (через запятую)</label>
                                    <input type="text" id="keywords" class="form-control" value="блог, портфолио, верстка, bootstrap, html, css, javascript">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="sitemap">
                                        <label class="form-check-label fw-bold" for="sitemap">Генерировать sitemap.xml</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="robots">
                                        <label class="form-check-label fw-bold" for="robots">Разрешить индексацию</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="home-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Главная страница — герой и шаги</h3>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="homePageMode" class="form-label fw-bold">Что открывается по умолчанию</label>
                                    <select id="homePageMode" class="form-select">
                                        <option value="main" selected>Шаблон главной страницы</option>
                                        <option value="static">Статическая страница (лендинг «home» из раздела «Страницы»)</option>
                                    </select>
                                    <div class="small text-muted mt-1">Если выбрана статическая страница, а она не активна — посетитель увидит 404.</div>
                                </div>
                                <div class="col-12">
                                    <label for="heroTitle" class="form-label fw-bold">Заголовок героя</label>
                                    <input type="text" id="heroTitle" class="form-control" value="Личный блог о веб-разработке и дизайне" maxlength="240">
                                </div>
                                <div class="col-12">
                                    <label for="heroSubtitle" class="form-label fw-bold">Подзаголовок героя</label>
                                    <textarea id="heroSubtitle" class="form-control" rows="2" maxlength="500">Здесь я делюсь опытом, статьями и проектами. Всё самое интересное — в одном месте.</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="homeFeaturedCount" class="form-label fw-bold">Статей в разделе «Свежие статьи»</label>
                                    <input type="number" id="homeFeaturedCount" class="form-control" min="1" max="12" value="3">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroPrimaryText" class="form-label fw-bold">Текст главной кнопки</label>
                                    <input type="text" id="heroPrimaryText" class="form-control" value="Читать блог" maxlength="80">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroPrimaryUrl" class="form-label fw-bold">Ссылка главной кнопки</label>
                                    <input type="text" id="heroPrimaryUrl" class="form-control" value="/blog.html" maxlength="240" placeholder="/blog.html или https://…">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroSecondaryText" class="form-label fw-bold">Текст второй кнопки</label>
                                    <input type="text" id="heroSecondaryText" class="form-control" value="Заказать проект" maxlength="80">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroSecondaryUrl" class="form-label fw-bold">Ссылка второй кнопки</label>
                                    <input type="text" id="heroSecondaryUrl" class="form-control" value="/order.html" maxlength="240" placeholder="/order.html или https://…">
                                </div>
                                <div class="col-md-4">
                                    <label for="step1Title" class="form-label fw-bold">Шаг 1 — заголовок</label>
                                    <input type="text" id="step1Title" class="form-control" value="Оставляете заявку" maxlength="160">
                                </div>
                                <div class="col-md-8">
                                    <label for="step1Text" class="form-label fw-bold">Шаг 1 — описание</label>
                                    <input type="text" id="step1Text" class="form-control" value="Заполняете форму на странице заказа — 2 минуты" maxlength="300">
                                </div>
                                <div class="col-md-4">
                                    <label for="step2Title" class="form-label fw-bold">Шаг 2 — заголовок</label>
                                    <input type="text" id="step2Title" class="form-control" value="Обсуждаем детали" maxlength="160">
                                </div>
                                <div class="col-md-8">
                                    <label for="step2Text" class="form-label fw-bold">Шаг 2 — описание</label>
                                    <input type="text" id="step2Text" class="form-control" value="Связываюсь с вами в течение дня и уточняем задачу" maxlength="300">
                                </div>
                                <div class="col-md-4">
                                    <label for="step3Title" class="form-label fw-bold">Шаг 3 — заголовок</label>
                                    <input type="text" id="step3Title" class="form-control" value="Получаете результат" maxlength="160">
                                </div>
                                <div class="col-md-8">
                                    <label for="step3Text" class="form-label fw-bold">Шаг 3 — описание</label>
                                    <input type="text" id="step3Text" class="form-control" value="Готовый сайт точно в срок и с гарантией" maxlength="300">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="headings-pane" role="tabpanel" aria-labelledby="headings-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Заголовки страниц</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="blogTitle" class="form-label fw-bold">Блог — заголовок</label>
                                    <input type="text" id="blogTitle" class="form-control" value="Блог" maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="blogLead" class="form-label fw-bold">Блог — подзаголовок</label>
                                    <input type="text" id="blogLead" class="form-control" value="Последние записи и полезные материалы" maxlength="200">
                                </div>
                                <div class="col-md-6">
                                    <label for="portfolioTitle" class="form-label fw-bold">Портфолио — заголовок</label>
                                    <input type="text" id="portfolioTitle" class="form-control" value="Портфолио" maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="portfolioLead" class="form-label fw-bold">Портфолио — подзаголовок</label>
                                    <input type="text" id="portfolioLead" class="form-control" value="Мои лучшие работы и проекты" maxlength="200">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactsTitle" class="form-label fw-bold">Контакты — заголовок</label>
                                    <input type="text" id="contactsTitle" class="form-control" value="Контакты" maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactsLead" class="form-label fw-bold">Контакты — подзаголовок</label>
                                    <input type="text" id="contactsLead" class="form-control" value="Буду рад связи и сотрудничеству" maxlength="200">
                                </div>
                                <div class="col-md-6">
                                    <label for="orderTitle" class="form-label fw-bold">Заказать — заголовок</label>
                                    <input type="text" id="orderTitle" class="form-control" value="Заказать" maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="orderLead" class="form-label fw-bold">Заказать — подзаголовок</label>
                                    <input type="text" id="orderLead" class="form-control" value="Расскажите о своей задаче — я свяжусь с вами" maxlength="200">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="mail-pane" role="tabpanel" aria-labelledby="mail-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-2">Макеты email-писем по формам</h3>
                            <p class="small text-muted mb-4">
                                Доступные подстановки: <code>{name}</code> — имя, <code>{email}</code>, <code>{phone}</code>,
                                <code>{subject}</code> — тема обращения, <code>{message}</code> — текст,
                                <code>{article}</code> — slug статьи (для комментариев), <code>{type}</code> — тип проекта и
                                <code>{budget}</code> — бюджет (для заявки). Для заявки также:
                                <code>{calc_features}</code> — выбранные в калькуляторе функции текущего типа сайта
                                (с ценами и сроками), <code>{calc_total}</code> — итог расчёта.
                            </p>

                            <h4 class="h6 fw-bold mb-3"><i class="bi bi-send text-orange"></i> Форма заказа</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="mailOrderSubject" class="form-label fw-bold">Тема письма</label>
                                    <input type="text" id="mailOrderSubject" class="form-control" maxlength="200" value="Новая заявка с сайта">
                                </div>
                                <div class="col-12">
                                    <label for="mailOrderBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailOrderBody" class="form-control" rows="6" maxlength="5000">Имя: {name}
Email: {email}
Телефон: {phone}
Тип: {type}
Бюджет: {budget}
Сообщение: {message}</textarea>
                                </div>
                            </div>

                            <h4 class="h6 fw-bold mb-3"><i class="bi bi-chat-left-dots text-orange"></i> Форма обратной связи</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="mailFeedbackSubject" class="form-label fw-bold">Тема письма</label>
                                    <input type="text" id="mailFeedbackSubject" class="form-control" maxlength="200" value="Обратная связь с сайта">
                                </div>
                                <div class="col-12">
                                    <label for="mailFeedbackBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailFeedbackBody" class="form-control" rows="6" maxlength="5000">Имя: {name}
Email: {email}
Тема: {subject}
Сообщение: {message}</textarea>
                                </div>
                            </div>

                            <h4 class="h6 fw-bold mb-3"><i class="bi bi-chat-square-text text-orange"></i> Комментарии к статьям</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="mailCommentSubject" class="form-label fw-bold">Тема письма</label>
                                    <input type="text" id="mailCommentSubject" class="form-control" maxlength="200" value="Новый комментарий к статье">
                                </div>
                                <div class="col-12">
                                    <label for="mailCommentBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailCommentBody" class="form-control" rows="6" maxlength="5000">Имя: {name}
Email: {email}
Статья: {article}
Комментарий: {message}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-orange fw-bold"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="notifications-pane" role="tabpanel" aria-labelledby="notifications-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Настройки уведомлений</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="notifyOrders">
                                        <label class="form-check-label fw-bold" for="notifyOrders">Новые заказы</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="notifyMessages">
                                        <label class="form-check-label fw-bold" for="notifyMessages">Новые сообщения</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="notifyComments">
                                        <label class="form-check-label fw-bold" for="notifyComments">Новые комментарии</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="notifyUsers">
                                        <label class="form-check-label fw-bold" for="notifyUsers">Регистрация пользователей</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="consultant-pane" role="tabpanel" aria-labelledby="consultant-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Онлайн-консультант</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="consultantEnabled">
                                        <label class="form-check-label fw-bold" for="consultantEnabled">Включить консультанта</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantWorkingHours" class="form-label fw-bold">Время работы</label>
                                    <input type="text" id="consultantWorkingHours" class="form-control" value="09:00-20:00" placeholder="09:00-20:00">
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantButtonColor" class="form-label fw-bold">Цвет кнопки</label>
                                    <input type="color" id="consultantButtonColor" class="form-control form-control-color" value="#ff8c1a">
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantButtonTextColor" class="form-label fw-bold">Цвет текста кнопки</label>
                                    <input type="color" id="consultantButtonTextColor" class="form-control form-control-color" value="#ffffff">
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantButtonPosition" class="form-label fw-bold">Расположение кнопки</label>
                                    <select id="consultantButtonPosition" class="form-select">
                                        <option value="right" selected>Справа</option>
                                        <option value="left">Слева</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantWelcomeMessage" class="form-label fw-bold">Приветствие</label>
                                    <input type="text" id="consultantWelcomeMessage" class="form-control" value="Здравствуйте! Чем могу помочь?">
                                </div>
                                <div class="col-md-6">
                                    <label for="consultantNotificationEmail" class="form-label fw-bold">Email для уведомлений о новых вопросах</label>
                                    <input type="email" id="consultantNotificationEmail" class="form-control" placeholder="admin@example.com" maxlength="254">
                                </div>
                                <div class="col-12">
                                    <label for="consultantCensoredWords" class="form-label fw-bold">Слова для цензуры (через запятую)</label>
                                    <textarea id="consultantCensoredWords" class="form-control" rows="3">дурак, идиот, тупица</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="contacts-pane" role="tabpanel" aria-labelledby="contacts-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Контактные данные и социальные сети</h3>
                            <div class="row g-3">
                                <?php
                                $contactAdminFields = [
                                    ['contactAddress', 'contactAddressEnabled', 'contactAddressLabel', 'Адрес', 'text', 'г. Самара, ул. Примерная, 1'],
                                    ['contactHours', 'contactHoursEnabled', 'contactHoursLabel', 'Часы работы', 'text', 'Пн-Пт: 9:00 — 18:00'],
                                    ['contactEmail', 'contactEmailEnabled', 'contactEmailLabel', 'Email', 'email', 'hello@moyblog.ru'],
                                    ['contactPhone', 'contactPhoneEnabled', 'contactPhoneLabel', 'Телефон', 'tel', '+7 (900) 123-45-67'],
                                ];
                                foreach ($contactAdminFields as [$valueId, $enabledId, $labelId, $fallbackLabel, $type, $value]):
                                ?>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="<?= $enabledId ?>" checked>
                                            <label class="form-check-label fw-bold" for="<?= $enabledId ?>">Показывать карточку</label>
                                        </div>
                                        <label for="<?= $labelId ?>" class="form-label">Название поля</label>
                                        <input type="text" id="<?= $labelId ?>" class="form-control mb-2" maxlength="80" value="<?= htmlspecialchars($fallbackLabel, ENT_QUOTES, 'UTF-8') ?>">
                                        <label for="<?= $valueId ?>" class="form-label">Содержимое</label>
                                        <input type="<?= $type ?>" id="<?= $valueId ?>" class="form-control" maxlength="254" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <?php
                                $socialAdminFields = [
                                    ['socialTelegram', 'socialTelegramEnabled', 'socialTelegramLabel', 'Telegram', 'https://t.me/username'],
                                    ['socialVk', 'socialVkEnabled', 'socialVkLabel', 'VK', 'https://vk.com/username'],
                                    ['socialGithub', 'socialGithubEnabled', 'socialGithubLabel', 'GitHub', 'https://github.com/username'],
                                    ['socialYoutube', 'socialYoutubeEnabled', 'socialYoutubeLabel', 'YouTube', 'https://youtube.com/@username'],
                                ];
                                foreach ($socialAdminFields as [$valueId, $enabledId, $labelId, $fallbackLabel, $placeholder]):
                                ?>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="<?= $enabledId ?>" checked>
                                            <label class="form-check-label fw-bold" for="<?= $enabledId ?>">Показывать кнопку</label>
                                        </div>
                                        <label for="<?= $labelId ?>" class="form-label">Название кнопки</label>
                                        <input type="text" id="<?= $labelId ?>" class="form-control mb-2" maxlength="80" value="<?= htmlspecialchars($fallbackLabel, ENT_QUOTES, 'UTF-8') ?>">
                                        <label for="<?= $valueId ?>" class="form-label">Ссылка</label>
                                        <input type="url" id="<?= $valueId ?>" class="form-control" placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="contacts-about-pane" role="tabpanel" aria-labelledby="contacts-about-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Блок «Обо мне» на странице контактов</h3>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="contactsAboutEnabled" checked>
                                <label class="form-check-label fw-bold" for="contactsAboutEnabled">Показывать блок над карточками контактов</label>
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-5">
                                    <label for="contactsAboutPhotoInput" class="form-label fw-bold">Большое фото</label>
                                    <input type="file" id="contactsAboutPhotoInput" class="form-control" accept="image/jpeg,image/png,image/webp">
                                    <button type="button" class="btn btn-orange fw-bold mt-2" id="contactsAboutPhotoUpload"><i class="bi bi-cloud-upload"></i> Загрузить фото</button>
                                    <span id="contactsAboutPhotoStatus" class="small text-muted d-block mt-2"></span>
                                    <input type="hidden" id="contactsAboutPhoto" value="">
                                </div>
                                <div class="col-lg-7">
                                    <label for="contactsAboutHtml" class="form-label fw-bold">HTML-содержимое</label>
                                    <div class="article-editor border rounded contacts-about-editor">
                                        <div class="article-editor-toolbar d-flex flex-wrap gap-1 p-2 border-bottom" role="toolbar" aria-label="Форматирование блока «Обо мне»">
                                            <select class="form-select form-select-sm article-editor-block" id="contactsAboutBlockFormat" aria-label="Формат блока">
                                                <option value="P">Обычный текст</option>
                                                <option value="H2">Заголовок 2</option>
                                                <option value="H3">Заголовок 3</option>
                                                <option value="H4">Заголовок 4</option>
                                                <option value="BLOCKQUOTE">Цитата</option>
                                            </select>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="bold" aria-label="Полужирный" title="Полужирный"><i class="bi bi-type-bold"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="italic" aria-label="Курсив" title="Курсив"><i class="bi bi-type-italic"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="underline" aria-label="Подчёркнутый" title="Подчёркнутый"><i class="bi bi-type-underline"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="insertUnorderedList" aria-label="Маркированный список" title="Маркированный список"><i class="bi bi-list-ul"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="insertOrderedList" aria-label="Нумерованный список" title="Нумерованный список"><i class="bi bi-list-ol"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-about-editor-command="removeFormat" aria-label="Очистить форматирование" title="Очистить форматирование"><i class="bi bi-eraser"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-dark ms-auto" id="contactsAboutSourceToggle" aria-pressed="false"><i class="bi bi-code-slash"></i> HTML</button>
                                        </div>
                                        <div id="contactsAboutEditor" class="article-editor-canvas" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Визуальное содержимое блока «Обо мне»"></div>
                                        <textarea id="contactsAboutHtml" class="form-control article-editor-source" rows="14" aria-label="HTML-код блока «Обо мне»" hidden><h2>Обо мне</h2><p>Расскажите посетителям о себе, своём опыте и подходе к работе.</p></textarea>
                                    </div>
                                    <div class="small text-muted mt-2">Используйте панель форматирования или включите режим HTML для редактирования кода.</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Настройки безопасности</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="siteStatus" class="form-label fw-bold">Режим сайта</label>
                                    <select id="siteStatus" class="form-select">
                                        <option value="online" selected>Работает</option>
                                        <option value="maintenance">Закрыт на техническое обслуживание</option>
                                    </select>
                                    <div class="small text-muted mt-1">Посетители вне IP-списка видят заглушку; админка и вход доступны всегда.</div>
                                </div>
                                <div class="col-12">
                                    <label for="maintenanceMessage" class="form-label fw-bold">Сообщение на время обслуживания</label>
                                    <textarea id="maintenanceMessage" class="form-control" rows="2" maxlength="300">Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="maintenanceStart" class="form-label fw-bold">Обслуживание с (от)</label>
                                    <input type="datetime-local" id="maintenanceStart" class="form-control">
                                    <div class="small text-muted mt-1">Если указаны обе границы — сайт закрывается автоматически в это время.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="maintenanceEnd" class="form-label fw-bold">Обслуживание до (по)</label>
                                    <input type="datetime-local" id="maintenanceEnd" class="form-control">
                                    <div class="small text-muted mt-1">После этого времени сайт откроется автоматически; на заглушке будет идти обратный отсчёт.</div>
                                </div>
                                <div class="col-12">
                                    <label for="maintenanceAllowedIps" class="form-label fw-bold">IP-адреса с доступом при обслуживании</label>
                                    <textarea id="maintenanceAllowedIps" class="form-control font-monospace" rows="2" placeholder="127.0.0.1&#10;::1"></textarea>
                                    <div class="small text-muted mt-1">По одному IP на строку (или через запятую). Эти посетители видят обычный сайт в режиме обслуживания. Оставьте пустым, чтобы закрыть сайт для всех.</div>
                                </div>
                                <div class="col-12">
                                    <label for="blockedIps" class="form-label fw-bold">IP-адреса без доступа (блокировка)</label>
                                    <textarea id="blockedIps" class="form-control font-monospace" rows="2" placeholder="203.0.113.10&#10;198.51.100.0/24"></textarea>
                                    <div class="small text-muted mt-1">По одному IP или подсети (CIDR) на строку. Посетители из списка видят страницу 403.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="maxLoginAttempts" class="form-label fw-bold">Попытки входа до блокировки</label>
                                    <select id="maxLoginAttempts" class="form-select">
                                        <option value="3">3 попытки</option>
                                        <option value="5" selected>5 попыток</option>
                                        <option value="10">10 попыток</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="lockoutMinutes" class="form-label fw-bold">Время блокировки (минуты)</label>
                                    <input type="number" id="lockoutMinutes" class="form-control" min="1" max="1440" value="15">
                                </div>
                                <div class="col-md-6">
                                    <label for="adminSessionHours" class="form-label fw-bold">Срок сессии админки (часы)</label>
                                    <input type="number" id="adminSessionHours" class="form-control" min="1" max="168" value="8">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="authentication-pane" role="tabpanel" aria-labelledby="authentication-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Аутентификация</h3>
                            <div class="alert alert-info">Настройки сохраняются в базе данных. Для Яндекс OAuth укажите redirect URI из консоли разработчика Яндекса.</div>
                            <div class="row g-3">
                                <div class="col-md-6"><label for="adminLoginEmail" class="form-label fw-bold">Email входа в админ-панель</label><input type="email" id="adminLoginEmail" class="form-control" autocomplete="username"></div>
                                <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="auth2faEnabled"><label class="form-check-label fw-bold" for="auth2faEnabled">Разрешить двухфакторную аутентификацию (TOTP)</label></div></div>
                                <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="yandexAuthEnabled"><label class="form-check-label fw-bold" for="yandexAuthEnabled">Вход через Яндекс</label></div></div>
                                <div class="col-md-6"><label for="yandexClientId" class="form-label fw-bold">ID приложения</label><input type="text" id="yandexClientId" class="form-control"></div>
                                <div class="col-md-6"><label for="yandexClientSecret" class="form-label fw-bold">Секретный ключ</label><input type="password" id="yandexClientSecret" class="form-control" autocomplete="new-password"></div>
                                <div class="col-12"><label for="yandexRedirectUri" class="form-label fw-bold">Redirect URI</label><input type="url" id="yandexRedirectUri" class="form-control"></div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="logs-pane" role="tabpanel" aria-labelledby="logs-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Система логирования</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsEnabled">
                                        <label class="form-check-label fw-bold" for="logsEnabled">Вести журнал событий</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsAutoClean">
                                        <label class="form-check-label fw-bold" for="logsAutoClean">Автоматическая очистка по сроку хранения</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="logsRetentionDays" class="form-label fw-bold">Срок хранения записей (дней)</label>
                                    <input type="number" id="logsRetentionDays" class="form-control" min="1" max="3650" value="30">
                                </div>
                            </div>

                            <h4 class="h6 fw-bold mt-4 mb-3">Что записывать в журнал</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsAuth">
                                        <label class="form-check-label fw-bold" for="logsAuth">Входы и регистрации (auth)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsForms">
                                        <label class="form-check-label fw-bold" for="logsForms">Отправка форм (заявки, сообщения, комментарии)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsSecurity">
                                        <label class="form-check-label fw-bold" for="logsSecurity">Блокировки и отказы в доступе (security)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" checked id="logsErrors">
                                        <label class="form-check-label fw-bold" for="logsErrors">Ошибки сервера 500 (errors)</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>

                            <h4 class="h6 fw-bold mt-5 mb-3">Последние записи журнала <span class="text-muted fw-normal" id="logsTotal"></span></h4>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="logsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Время</th>
                                            <th>Тип</th>
                                            <th>Событие</th>
                                            <th>Пользователь</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="5" class="text-center text-muted">Загрузка журнала…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-danger mt-3" id="clearLogsBtn"><i class="bi bi-trash"></i> Очистить журнал</button>
                        </section>

                        <section class="tab-pane fade" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Параметры отправки почты (SMTP)</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="smtpEnabled">
                                        <label class="form-check-label fw-bold" for="smtpEnabled">Отправлять письма через SMTP (снят тумблер — обычная PHP-отправка mail())</label>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-orange fw-bold" id="testEmailBtn"><i class="bi bi-send"></i> Тест отправки</button>
                                    <span class="ms-2 small text-muted" id="testEmailStatus"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpHost" class="form-label fw-bold">SMTP-сервер</label>
                                    <input type="text" id="smtpHost" class="form-control" placeholder="smtp.example.com">
                                </div>
                                <div class="col-md-3">
                                    <label for="smtpPort" class="form-label fw-bold">Порт</label>
                                    <input type="number" id="smtpPort" class="form-control" min="1" max="65535" value="587" placeholder="587">
                                </div>
                                <div class="col-md-3">
                                    <label for="smtpEncryption" class="form-label fw-bold">Шифрование</label>
                                    <select id="smtpEncryption" class="form-select">
                                        <option value="tls" selected>STARTTLS</option>
                                        <option value="ssl">SSL/TLS</option>
                                        <option value="none">Без шифрования</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpUsername" class="form-label fw-bold">Имя пользователя</label>
                                    <input type="text" id="smtpUsername" class="form-control" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpPassword" class="form-label fw-bold">Пароль приложения</label>
                                     <input type="password" id="smtpPassword" class="form-control" autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpFromName" class="form-label fw-bold">Имя отправителя</label>
                                    <input type="text" id="smtpFromName" class="form-control" placeholder="МойБлог" value="МойБлог">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpFromEmail" class="form-label fw-bold">Email отправителя</label>
                                    <input type="email" id="smtpFromEmail" class="form-control" placeholder="no-reply@example.com" value="no-reply@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpRecipient" class="form-label fw-bold">Email получателя заявок</label>
                                    <input type="email" id="smtpRecipient" class="form-control" placeholder="admin@example.com" value="admin@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpReplyTo" class="form-label fw-bold">Ответы направлять на</label>
                                    <input type="email" id="smtpReplyTo" class="form-control" placeholder="support@example.com">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="articles-pane" role="tabpanel" aria-labelledby="articles-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Настройки статей</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="articlePerPage" class="form-label fw-bold">Статей на странице</label>
                                    <input type="number" id="articlePerPage" class="form-control" min="1" max="100" value="12">
                                </div>
                                <div class="col-md-6">
                                    <label for="articlesPerRow" class="form-label fw-bold">Статей в ряд</label>
                                    <select id="articlesPerRow" class="form-select">
                                        <option value="1">Одна (во всю ширину)</option>
                                        <option value="2">Две (половина ширины)</option>
                                        <option value="3" selected>Три</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="blogSidebar" class="form-label fw-bold">Сайдбар на странице списка</label>
                                    <select id="blogSidebar" class="form-select">
                                        <option value="none" selected>Выключен</option>
                                        <option value="left">Слева</option>
                                        <option value="right">Справа</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="articlePageSidebar" class="form-label fw-bold">Сайдбар на странице статьи</label>
                                    <select id="articlePageSidebar" class="form-select">
                                        <option value="left">Слева</option>
                                        <option value="right" selected>Справа</option>
                                        <option value="none">Выключен</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="articleSortOrder" class="form-label fw-bold">Сортировка статей</label>
                                    <select id="articleSortOrder" class="form-select">
                                        <option value="newest">Сначала новые</option>
                                        <option value="oldest">Сначала старые</option>
                                        <option value="popular">По популярности</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="articleExcerptLength" class="form-label fw-bold">Длина анонса (символов)</label>
                                    <input type="number" id="articleExcerptLength" class="form-control" min="50" max="2000" value="300">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleCommentsEnabled">
                                        <label class="form-check-label fw-bold" for="articleCommentsEnabled">Разрешить комментарии</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="commentsAccess" class="form-label fw-bold">Кто видит комментарии</label>
                                    <select id="commentsAccess" class="form-select">
                                        <option value="guests">Все посетители</option>
                                        <option value="users" selected>Только авторизованные</option>
                                        <option value="staff">Только персонал</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleTagsEnabled">
                                        <label class="form-check-label fw-bold" for="articleTagsEnabled">Показывать теги</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleCategoriesEnabled">
                                        <label class="form-check-label fw-bold" for="articleCategoriesEnabled">Показывать рубрики</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleReadTime">
                                        <label class="form-check-label fw-bold" for="articleReadTime">Показывать время чтения</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleRelatedEnabled">
                                        <label class="form-check-label fw-bold" for="articleRelatedEnabled">Показывать похожие статьи</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="articleShareButtons">
                                        <label class="form-check-label fw-bold" for="articleShareButtons">Кнопки «Поделиться»</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="portfolio-pane" role="tabpanel" aria-labelledby="portfolio-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Настройки портфолио</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="portfolioPerPage" class="form-label fw-bold">Работ на странице</label>
                                    <input type="number" id="portfolioPerPage" class="form-control" min="1" max="100" value="12">
                                </div>
                                <div class="col-md-6">
                                    <label for="projectsPerRow" class="form-label fw-bold">Работ в ряд</label>
                                    <select id="projectsPerRow" class="form-select">
                                        <option value="1">Одна (во всю ширину)</option>
                                        <option value="2">Две (половина ширины)</option>
                                        <option value="3" selected>Три</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="portfolioLayout" class="form-label fw-bold">Тип отображения</label>
                                    <select id="portfolioLayout" class="form-select">
                                        <option value="grid">Сетка</option>
                                        <option value="masonry">Мозаика</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="portfolioSidebar" class="form-label fw-bold">Сайдбар на странице портфолио</label>
                                    <select id="portfolioSidebar" class="form-select">
                                        <option value="none" selected>Выключен</option>
                                        <option value="left">Слева</option>
                                        <option value="right">Справа</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="projectPageSidebar" class="form-label fw-bold">Сайдбар на странице проекта</label>
                                    <select id="projectPageSidebar" class="form-select">
                                        <option value="left">Слева</option>
                                        <option value="right" selected>Справа</option>
                                        <option value="none">Выключен (под описанием)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowTags">
                                        <label class="form-check-label fw-bold" for="portfolioShowTags">Показывать технологии</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowDate">
                                        <label class="form-check-label fw-bold" for="portfolioShowDate">Показывать год</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowClient">
                                        <label class="form-check-label fw-bold" for="portfolioShowClient">Показывать клиента</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowExtraFields">
                                        <label class="form-check-label fw-bold" for="portfolioShowExtraFields">Показывать дополнительные поля</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="templates-pane" role="tabpanel" aria-labelledby="templates-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Шаблоны сайта</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="frontendTemplate" class="form-label fw-bold">Шаблон публичной части</label>
                                    <select id="frontendTemplate" class="form-select" data-template-area="frontend">
                                        <option value="default">default</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="adminTemplate" class="form-label fw-bold">Шаблон админ-панели</label>
                                    <select id="adminTemplate" class="form-select" data-template-area="admin">
                                        <option value="default">default</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-muted small mt-3 mb-0">Доступны только шаблоны, присутствующие в папках приложения. После смены сохраните настройки и обновите страницу.</p>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="appearance-pane" role="tabpanel" aria-labelledby="appearance-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Оформление сайта и админки</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="themePrimary" class="form-label fw-bold">Основной цвет</label>
                                    <input type="color" id="themePrimary" class="form-control form-control-color w-100 py-1" value="#ff8c1a">
                                </div>
                                <div class="col-md-6">
                                    <label for="themeBackground" class="form-label fw-bold">Фон страницы</label>
                                    <input type="color" id="themeBackground" class="form-control form-control-color w-100 py-1" value="#ffffff">
                                </div>
                                <div class="col-md-6">
                                    <label for="themeBlockBg" class="form-label fw-bold">Фон блоков (карточек)</label>
                                    <input type="color" id="themeBlockBg" class="form-control form-control-color w-100 py-1" value="#ffffff">
                                </div>
                                <div class="col-md-6">
                                    <label for="themeSidebarBg" class="form-label fw-bold">Фон сайдбаров</label>
                                    <input type="color" id="themeSidebarBg" class="form-control form-control-color w-100 py-1" value="#ffffff">
                                </div>
                                <div class="col-md-6">
                                    <label for="themeText" class="form-label fw-bold">Цвет текста</label>
                                    <input type="color" id="themeText" class="form-control form-control-color w-100 py-1" value="#353535">
                                </div>
                            </div>
                            <hr class="my-4">
                            <h6 class="fw-bold text-dark mb-3">Размеры</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="themeFontSize" class="form-label fw-bold">Размер текста, px (12–24)</label>
                                    <input type="number" id="themeFontSize" class="form-control" value="16" min="12" max="24" step="1">
                                </div>
                                <div class="col-md-4">
                                    <label for="themeNavSize" class="form-label fw-bold">Размер меню, px (10–24)</label>
                                    <input type="number" id="themeNavSize" class="form-control" value="16" min="10" max="24" step="1">
                                </div>
                                <div class="col-md-4">
                                    <label for="themeHeadingScale" class="form-label fw-bold">Размер заголовков, % (60–200)</label>
                                    <input type="number" id="themeHeadingScale" class="form-control" value="100" min="60" max="200" step="5">
                                </div>
                            </div>
                            <hr class="my-4">
                            <h6 class="fw-bold text-dark mb-3">Шрифт</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="themeFontFamily" class="form-label fw-bold">Название шрифта</label>
                                    <input type="text" id="themeFontFamily" class="form-control" list="fontFamilyList" placeholder="Например, Open Sans (пусто — стандартный Montserrat)" autocomplete="off">
                                    <datalist id="fontFamilyList">
                                        <option value="Open Sans"></option>
                                        <option value="Roboto"></option>
                                        <option value="Raleway"></option>
                                        <option value="Lato"></option>
                                        <option value="Inter"></option>
                                        <option value="PT Sans"></option>
                                        <option value="Montserrat"></option>
                                    </datalist>
                                </div>
                                <div class="col-md-6">
                                    <label for="themeFontUrl" class="form-label fw-bold">Ссылка на шрифт (CDN)</label>
                                    <input type="url" id="themeFontUrl" class="form-control" placeholder="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" autocomplete="off">
                                </div>
                            </div>
                            <hr class="my-4">
                            <label class="form-label fw-bold d-block mb-2">Или загрузить файл шрифта (woff, woff2, ttf, otf — до 2 МБ)</label>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <input type="file" id="themeFontFileInput" class="form-control" style="max-width: 24rem;" accept=".woff,.woff2,.ttf,.otf">
                                <button type="button" class="btn btn-orange fw-bold" id="fontUploadBtn"><i class="bi bi-cloud-upload"></i> Загрузить файл</button>
                                <span class="fw-bold text-muted" id="fontUploadStatus"></span>
                            </div>
                            <input type="hidden" id="themeFontFile" value="">
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>
                    </div>
                    </form>
                </div>
            </div>
