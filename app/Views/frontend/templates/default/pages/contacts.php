<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = $contactsHeading ?? 'Контакты'; $headingLead = $contactsHeadingLead ?? 'Буду рад связи и сотрудничеству'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= КОНТАКТЫ ================= -->
<?php $contactSettings = is_array($contacts ?? null) ? $contacts : []; ?>
<section class="py-5 bg-orange-light">
        <div class="container">
            <?php
            $aboutHtml = (string) ($contactSettings['contactsAboutHtml'] ?? '');
            $aboutPhoto = (string) ($contactSettings['contactsAboutPhoto'] ?? '');
            $aboutPhotoSafe = preg_match('#^uploads/contacts/[a-f0-9]{32}\.(?:jpg|png|webp)$#', $aboutPhoto) === 1;
            ?>
            <?php if ((string) ($contactSettings['contactsAboutEnabled'] ?? '1') === '1' && ($aboutHtml !== '' || $aboutPhotoSafe)): ?>
            <section class="contacts-about mb-5">
                <div class="row g-4 align-items-center">
                    <?php if ($aboutPhotoSafe): ?>
                    <div class="col-lg-5 text-center">
                        <img src="/uploads/contacts/<?= htmlspecialchars(basename($aboutPhoto), ENT_QUOTES, 'UTF-8') ?>" class="contacts-about-photo img-fluid" alt="Обо мне" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <?php if ($aboutHtml !== ''): ?>
                    <div class="<?= $aboutPhotoSafe ? 'col-lg-7' : 'col-12' ?> contacts-about-content">
                        <?= $aboutHtml ?>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
            <?php
            $contactCards = [
                ['contactAddress', 'contactAddressEnabled', 'contactAddressLabel', 'Адрес', 'г. Самара, ул. Примерная, 1', 'bi-geo-alt'],
                ['contactEmail', 'contactEmailEnabled', 'contactEmailLabel', 'Email', 'hello@moyblog.ru', 'bi-envelope'],
                ['contactPhone', 'contactPhoneEnabled', 'contactPhoneLabel', 'Телефон', '+7 (900) 123-45-67', 'bi-telephone'],
                ['contactHours', 'contactHoursEnabled', 'contactHoursLabel', 'Часы работы', 'Пн–Пт, 10:00–18:00', 'bi-clock'],
            ];
            ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($contactCards as [$valueKey, $enabledKey, $labelKey, $fallbackLabel, $fallbackValue, $icon]): ?>
                    <?php if ((string) ($contactSettings[$enabledKey] ?? '1') === '1'): ?>
                    <div class="col-md-6 col-lg-3 text-center">
                        <div class="contact-item p-4 h-100">
                            <i class="bi <?= $icon ?> display-6 text-orange"></i>
                            <h5 class="fw-bold mt-3"><?= htmlspecialchars((string) ($contactSettings[$labelKey] ?? $fallbackLabel), ENT_QUOTES, 'UTF-8') ?></h5>
                            <p class="fw-bold mb-0"><?= htmlspecialchars((string) ($contactSettings[$valueKey] ?? $fallbackValue), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Форма обратной связи -->
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <h5 class="fw-bold text-center mb-4">Напишите мне</h5>
                    <form action="/api/forms/feedback" method="POST" class="card border-0 shadow-sm p-4 order-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contactName" class="form-label fw-bold">Ваше имя</label>
                                <input type="text" class="form-control" id="contactName" name="name" placeholder="Иван Иванов" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="email" placeholder="ivan@example.ru" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactSubject" class="form-label fw-bold">Тема</label>
                                <input type="text" class="form-control" id="contactSubject" name="subject" placeholder="Тема сообщения">
                            </div>
                            <div class="col-md-6">
                                <label for="contactPhone" class="form-label fw-bold">Телефон (необязательно)</label>
                                <input type="tel" class="form-control" id="contactPhone" name="phone" placeholder="+7 (900) 123-45-67">
                            </div>
                            <div class="col-12">
                                <label for="contactMessage" class="form-label fw-bold">Сообщение</label>
                                <textarea class="form-control" id="contactMessage" name="message" rows="4" placeholder="Введите текст сообщения..." required></textarea>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-orange btn-lg fw-bold px-5">
                                    Отправить сообщение <i class="bi bi-envelope-paper"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Социальные сети (значения — из настроек сайта, показываются только заполненные) -->
            <?php
            $socialNetworks = [
                'Telegram' => ['socialTelegram', 'socialTelegramEnabled', 'socialTelegramLabel', 'Telegram', 'bi-telegram'],
                'Vk' => ['socialVk', 'socialVkEnabled', 'socialVkLabel', 'VK', 'bi-chat-dots'],
                'Github' => ['socialGithub', 'socialGithubEnabled', 'socialGithubLabel', 'GitHub', 'bi-github'],
                'Youtube' => ['socialYoutube', 'socialYoutubeEnabled', 'socialYoutubeLabel', 'YouTube', 'bi-youtube'],
            ];
            $hasSocialLinks = false;
            foreach ($socialNetworks as $networkName => $networkConfig) {
                $networkUrl = trim((string) ($contactSettings[$networkConfig[0]] ?? ''));
                $networkEnabled = (string) ($contactSettings[$networkConfig[1]] ?? '1') === '1';
                if ($networkEnabled && $networkUrl !== '' && preg_match('#^https?://#i', $networkUrl)) {
                    $hasSocialLinks = true;
                    break;
                }
            }
            ?>
            <?php if ($hasSocialLinks): ?>
            <div class="text-center mt-5">
                <h5 class="fw-bold mb-4">Я в социальных сетях</h5>
                <div class="contact-social-links d-flex flex-wrap justify-content-center align-items-center gap-2">
                <?php foreach ($socialNetworks as $networkName => $networkConfig): ?>
                    <?php $networkUrl = trim((string) ($contactSettings[$networkConfig[0]] ?? '')); ?>
                    <?php $networkEnabled = (string) ($contactSettings[$networkConfig[1]] ?? '1') === '1'; ?>
                    <?php if ($networkEnabled && $networkUrl !== '' && preg_match('#^https?://#i', $networkUrl)): ?>
                        <a href="<?= htmlspecialchars($networkUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-orange btn-lg rounded-circle me-2 social-btn" aria-label="<?= htmlspecialchars((string) ($contactSettings[$networkConfig[2]] ?? $networkConfig[3]), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars((string) ($contactSettings[$networkConfig[2]] ?? $networkConfig[3]), ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi <?= htmlspecialchars($networkConfig[4], ENT_QUOTES, 'UTF-8') ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
    
