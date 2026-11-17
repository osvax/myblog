<!-- ================= ШАПКА / МЕНЮ ================= -->
    

    <!-- ================= ЗАГОЛОВОК СТРАНИЦЫ ================= -->
    <?php $heading = $contactsHeading ?? 'Контакты'; $headingLead = $contactsHeadingLead ?? 'Буду рад связи и сотрудничеству'; require APP_ROOT . '/app/Views/frontend/templates/default/partials/page-heading.php'; ?>

    <!-- Волна: оранжевый → светло-оранжевый -->
    <?php $waveColors = ['#ff8c1a', '#fff3e6']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= КОНТАКТЫ ================= -->
<?php $contactSettings = is_array($contacts ?? null) ? $contacts : []; ?>
<section class="py-5 bg-orange-light">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="contact-item p-4 h-100">
                        <i class="bi bi-geo-alt display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Адрес</h5>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($contactSettings['contactAddress'] ?? 'г. Самара, ул. Примерная, 1', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="contact-item p-4 h-100">
                        <i class="bi bi-envelope display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Email</h5>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($contactSettings['contactEmail'] ?? 'hello@moyblog.ru', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="contact-item p-4 h-100">
                        <i class="bi bi-telephone display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Телефон</h5>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($contactSettings['contactPhone'] ?? '+7 (900) 123-45-67', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 text-center">
                    <div class="contact-item p-4 h-100">
                        <i class="bi bi-clock display-6 text-orange"></i>
                        <h5 class="fw-bold mt-3">Часы работы</h5>
                        <p class="fw-bold mb-0"><?= htmlspecialchars($contactSettings['contactHours'] ?? 'Пн–Пт, 10:00–18:00', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
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
                'Telegram' => ['socialTelegram', 'bi-telegram'],
                'Vk' => ['socialVk', 'bi-chat-dots'],
                'Github' => ['socialGithub', 'bi-github'],
                'Youtube' => ['socialYoutube', 'bi-youtube'],
            ];
            $hasSocialLinks = false;
            foreach ($socialNetworks as $networkName => $networkConfig) {
                $networkUrl = trim((string) ($contactSettings[$networkConfig[0]] ?? ''));
                if ($networkUrl !== '' && preg_match('#^https?://#i', $networkUrl)) {
                    $hasSocialLinks = true;
                    break;
                }
            }
            ?>
            <?php if ($hasSocialLinks): ?>
            <div class="text-center mt-5">
                <h5 class="fw-bold mb-4">Я в социальных сетях</h5>
                <?php foreach ($socialNetworks as $networkName => $networkConfig): ?>
                    <?php $networkUrl = trim((string) ($contactSettings[$networkConfig[0]] ?? '')); ?>
                    <?php if ($networkUrl !== '' && preg_match('#^https?://#i', $networkUrl)): ?>
                        <a href="<?= htmlspecialchars($networkUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-orange btn-lg rounded-circle me-2 social-btn" aria-label="<?= htmlspecialchars($networkName, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi <?= htmlspecialchars($networkConfig[1], ENT_QUOTES, 'UTF-8') ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Волна: светло-оранжевый → тёмный -->
    <?php $waveColors = ['#fff3e6', '#353535']; require APP_ROOT . '/app/Views/frontend/templates/default/partials/wave.php'; ?>

    <!-- ================= ПОДВАЛ ================= -->
    
