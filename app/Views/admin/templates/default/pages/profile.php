<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0"><i class="bi bi-person-circle me-2"></i>Профиль</h1>
</div>

<div id="profileError" class="alert alert-danger" style="display:none;"></div>
<div id="profileSuccess" class="alert alert-success" style="display:none;"></div>

<div class="row g-4">
    <!-- Левая колонка — аватар и основная информация -->
    <div class="col-lg-4">
        <section class="card admin-card mb-4">
            <div class="card-body text-center p-4">
                <div class="position-relative d-inline-block mb-3 text-center">
                    <img class="user-avatar-img" id="profileAvatar"
                         src="/uploads/profile/a0e4ff889b07b84c881942047b6aa626.jpg" alt="Аватар"
                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #f97316; display: block; margin: 0 auto;">
                    <label for="avatarFile" class="avatar-edit-btn" title="Изменить аватар">
                        <i class="bi bi-camera"></i>
                    </label>
                    <input type="file" id="avatarFile" accept="image/jpeg,image/png,image/webp" hidden>
                </div>
                <p class="small text-muted mb-2">JPEG, PNG или WebP, до 2 МБ</p>
                <button class="btn btn-orange btn-sm fw-bold w-100" type="button" id="uploadAvatar">
                    <i class="bi bi-cloud-arrow-up"></i> Загрузить аватар
                </button>
                <p class="small text-danger mt-2 mb-0" id="avatarError" role="alert" hidden></p>
                <p class="small text-success mt-2 mb-0" id="avatarSuccess" role="status" hidden></p>
                <hr>
                <h2 class="h5 fw-bold mb-1" id="profileCardName">Загрузка…</h2>
                <p class="text-muted mb-2 text-break" id="profileCardEmail"></p>
                <span class="badge bg-orange fs-6 mb-2" id="profileCardRole"></span>
                <div id="profileCardPosition" class="small text-muted mb-2"></div>
                <div class="text-start small text-muted">
                    <div id="profileCardLastVisit" class="mb-1"></div>
                    <div id="profileCardLastIp" class="mb-1"></div>
                    <div id="profileCardCreatedAt" class="mb-1"></div>
                </div>
            </div>
        </section>
    </div>

    <!-- Правая колонка — форма профиля -->
    <div class="col-lg-8">
        <section class="card admin-card">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0">Основные данные</h2>
            </div>
            <div class="card-body p-4">
                <form id="profileForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profileName">Имя</label>
                            <input class="form-control" id="profileName" name="name" maxlength="160" required>
                            <div class="invalid-feedback">Укажите имя (2–160 символов).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profileEmail">Email</label>
                            <input class="form-control" type="email" id="profileEmail" name="email" maxlength="254" disabled>
                            <div class="form-text text-muted">Email нельзя изменить</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profilePhone">Телефон</label>
                            <input class="form-control" type="tel" id="profilePhone" name="phone" maxlength="40" placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profilePosition">Должность</label>
                            <input class="form-control" id="profilePosition" name="position" maxlength="100" placeholder="Например, владелец сайта">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="profileAbout">О себе</label>
                            <textarea class="form-control" id="profileAbout" name="about" rows="3" maxlength="500" placeholder="Краткая информация о профиле"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" for="profileBio">Биография</label>
                            <textarea class="form-control" id="profileBio" name="bio" rows="5" placeholder="Расскажите подробнее о себе, опыте, интересах"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profileTimezone">Часовой пояс</label>
                            <select class="form-select" id="profileTimezone" name="timezone">
                                <option value="">По умолчанию</option>
                                <option value="Europe/Samara">Самара (UTC+4)</option>
                                <option value="Europe/Moscow">Москва (UTC+3)</option>
                                <option value="Europe/Volgograd">Волгоград (UTC+3)</option>
                                <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                                <option value="Asia/Novosibirsk">Новосибирск (UTC+7)</option>
                                <option value="Asia/Irkutsk">Иркутск (UTC+8)</option>
                                <option value="Asia/Vladivostok">Владивосток (UTC+10)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="profileLanguage">Язык интерфейса</label>
                            <select class="form-select" id="profileLanguage" name="language">
                                <option value="ru">Русский</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-orange fw-bold" type="submit">
                            <i class="bi bi-save me-1"></i> Сохранить профиль
                        </button>
                        <button class="btn btn-outline-secondary fw-bold" id="resetProfile" type="button">
                            Сбросить
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- История посещений -->
        <section class="card admin-card mt-4">
            <div class="card-header bg-white">
                <h2 class="h5 fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>История посещений</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="activityTable">
                        <thead>
                            <tr>
                                <th style="width:180px;">Дата и время</th>
                                <th>Страница</th>
                                <th style="width:140px;">IP-адрес</th>
                                <th style="width:200px;" class="d-none d-xl-table-cell">Браузер</th>
                            </tr>
                        </thead>
                        <tbody id="activityBody">
                            <tr><td colspan="4" class="text-center text-muted py-4">Загрузка…</td></tr>
                        </tbody>
                    </table>
                </div>
                <!-- Пагинация -->
                <div class="d-flex justify-content-center align-items-center gap-2 p-3 border-top" id="activityPagination" hidden>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="prevPageBtn">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="small text-muted" id="paginationInfo"></span>
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="nextPageBtn">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
