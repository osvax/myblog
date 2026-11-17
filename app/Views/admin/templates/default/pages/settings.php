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
                            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-pane" type="button" role="tab" aria-controls="seo-pane" aria-selected="false" title="SEO"><i class="bi bi-search"></i></button>
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
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab" aria-controls="security-pane" aria-selected="false" title="Безопасность"><i class="bi bi-shield-lock"></i></button>
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
                                    <input type="url" id="heroPrimaryUrl" class="form-control" value="/blog.html" maxlength="240">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroSecondaryText" class="form-label fw-bold">Текст второй кнопки</label>
                                    <input type="text" id="heroSecondaryText" class="form-control" value="Заказать проект" maxlength="80">
                                </div>
                                <div class="col-md-6">
                                    <label for="heroSecondaryUrl" class="form-label fw-bold">Ссылка второй кнопки</label>
                                    <input type="url" id="heroSecondaryUrl" class="form-control" value="/order.html" maxlength="240">
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
                                    <input type="text" id="mailOrderSubject" class="form-control" maxlength="200">
                                </div>
                                <div class="col-12">
                                    <label for="mailOrderBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailOrderBody" class="form-control" rows="6" maxlength="5000"></textarea>
                                </div>
                            </div>

                            <h4 class="h6 fw-bold mb-3"><i class="bi bi-chat-left-dots text-orange"></i> Форма обратной связи</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="mailFeedbackSubject" class="form-label fw-bold">Тема письма</label>
                                    <input type="text" id="mailFeedbackSubject" class="form-control" maxlength="200">
                                </div>
                                <div class="col-12">
                                    <label for="mailFeedbackBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailFeedbackBody" class="form-control" rows="6" maxlength="5000"></textarea>
                                </div>
                            </div>

                            <h4 class="h6 fw-bold mb-3"><i class="bi bi-chat-square-text text-orange"></i> Комментарии к статьям</h4>
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <label for="mailCommentSubject" class="form-label fw-bold">Тема письма</label>
                                    <input type="text" id="mailCommentSubject" class="form-control" maxlength="200">
                                </div>
                                <div class="col-12">
                                    <label for="mailCommentBody" class="form-label fw-bold">Текст письма</label>
                                    <textarea id="mailCommentBody" class="form-control" rows="6" maxlength="5000"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-orange fw-bold"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="seo-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">SEO настройки</h3>
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
                                <div class="col-md-6">
                                    <label for="contactAddress" class="form-label fw-bold">Адрес</label>
                                    <input type="text" id="contactAddress" class="form-control" maxlength="160">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactHours" class="form-label fw-bold">Часы работы</label>
                                    <input type="text" id="contactHours" class="form-control" maxlength="120">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactEmail" class="form-label fw-bold">Публичный email</label>
                                    <input type="email" id="contactEmail" class="form-control" maxlength="254">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactPhone" class="form-label fw-bold">Телефон</label>
                                    <input type="tel" id="contactPhone" class="form-control" maxlength="40">
                                </div>
                                <div class="col-md-6">
                                    <label for="socialTelegram" class="form-label fw-bold">Ссылка на Telegram</label>
                                    <input type="url" id="socialTelegram" class="form-control" placeholder="https://t.me/username">
                                </div>
                                <div class="col-md-6">
                                    <label for="socialVk" class="form-label fw-bold">Ссылка на VK</label>
                                    <input type="url" id="socialVk" class="form-control" placeholder="https://vk.com/username">
                                </div>
                                <div class="col-md-6">
                                    <label for="socialGithub" class="form-label fw-bold">Ссылка на GitHub</label>
                                    <input type="url" id="socialGithub" class="form-control" placeholder="https://github.com/username">
                                </div>
                                <div class="col-md-6">
                                    <label for="socialYoutube" class="form-label fw-bold">Ссылка на YouTube</label>
                                    <input type="url" id="socialYoutube" class="form-control" placeholder="https://youtube.com/@username">
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
                                <div class="col-md-6">
                                    <label for="twoFactorMethod" class="form-label fw-bold">Двухфакторная аутентификация</label>
                                    <select id="twoFactorMethod" class="form-select">
                                        <option value="off" selected>Выключена</option>
                                        <option value="authenticator">Приложение-аутентификатор (TOTP)</option>
                                        <option value="sms">SMS-код</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="maintenanceMessage" class="form-label fw-bold">Сообщение на время обслуживания</label>
                                    <textarea id="maintenanceMessage" class="form-control" rows="2" maxlength="300">Сайт временно закрыт на техническое обслуживание. Скоро вернёмся!</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="maintenanceAllowedIps" class="form-label fw-bold">IP-адреса с доступом при обслуживании</label>
                                    <textarea id="maintenanceAllowedIps" class="form-control font-monospace" rows="2" placeholder="127.0.0.1&#10;::1"></textarea>
                                    <div class="small text-muted mt-1">По одному IP на строку (или через запятую). Эти посетители видят обычный сайт в режиме обслуживания.</div>
                                </div>
                                <div class="col-12">
                                    <label for="adminIpAllowlist" class="form-label fw-bold">Разрешённые IP для входа в админку</label>
                                     <textarea id="adminIpAllowlist" class="form-control font-monospace" rows="4"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="securityContactEmail" class="form-label fw-bold">Email для уведомлений о безопасности</label>
                                    <input type="email" id="securityContactEmail" class="form-control" placeholder="security@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="twoFactorPhone" class="form-label fw-bold">Телефон для SMS-кодов</label>
                                    <input type="tel" id="twoFactorPhone" class="form-control" placeholder="+7 900 000-00-00">
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
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="loginCaptcha">
                                        <label class="form-check-label fw-bold" for="loginCaptcha">Проверка CAPTCHA при входе</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="securityLoginAlerts" checked>
                                        <label class="form-check-label fw-bold" for="securityLoginAlerts">Уведомлять о новых входах</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>

                        <section class="tab-pane fade" id="email-pane" role="tabpanel" aria-labelledby="email-tab" tabindex="0">
                            <h3 class="h5 fw-bold mb-4">Параметры отправки почты (SMTP)</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="smtpHost" class="form-label fw-bold">SMTP-сервер</label>
                                    <input type="text" id="smtpHost" class="form-control" placeholder="smtp.example.com">
                                </div>
                                <div class="col-md-3">
                                    <label for="smtpPort" class="form-label fw-bold">Порт</label>
                                    <input type="number" id="smtpPort" class="form-control" min="1" max="65535" placeholder="587">
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
                                    <input type="text" id="smtpFromName" class="form-control" placeholder="МойБлог">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpFromEmail" class="form-label fw-bold">Email отправителя</label>
                                    <input type="email" id="smtpFromEmail" class="form-control" placeholder="no-reply@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtpRecipient" class="form-label fw-bold">Email получателя заявок</label>
                                    <input type="email" id="smtpRecipient" class="form-control" placeholder="admin@example.com">
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
                                        <input class="form-check-input" type="checkbox" checked id="articleFeaturedEnabled">
                                        <label class="form-check-label fw-bold" for="articleFeaturedEnabled">Показывать избранные статьи</label>
                                    </div>
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
                                    <label for="portfolioLayout" class="form-label fw-bold">Тип отображения</label>
                                    <select id="portfolioLayout" class="form-select">
                                        <option value="grid">Сетка</option>
                                        <option value="masonry">Мозаика</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowTags">
                                        <label class="form-check-label fw-bold" for="portfolioShowTags">Показывать теги</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowCategories">
                                        <label class="form-check-label fw-bold" for="portfolioShowCategories">Показывать рубрики</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowViews">
                                        <label class="form-check-label fw-bold" for="portfolioShowViews">Показывать счётчик просмотров</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowDate">
                                        <label class="form-check-label fw-bold" for="portfolioShowDate">Показывать дату</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" checked id="portfolioFeaturedEnabled">
                                        <label class="form-check-label fw-bold" for="portfolioFeaturedEnabled">Показывать избранные работы</label>
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
                                        <input class="form-check-input" type="checkbox" checked id="portfolioShowLink">
                                        <label class="form-check-label fw-bold" for="portfolioShowLink">Показывать ссылку на проект</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-orange fw-bold mt-4"><i class="bi bi-save"></i> Сохранить</button>
                        </section>
                    </div>
                    </form>
                </div>
            </div>
