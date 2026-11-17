<div class="card admin-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0">Онлайн-чат консультанта</h2>
                    <span class="badge bg-orange">Live</span>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0 consultant-admin-layout">
                        <aside class="col-lg-4 consultant-users-panel">
                            <div class="consultant-users-header">
                                <strong>Текущие пользователи</strong>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="consultantDeleteAnswered" disabled>
                                    <i class="bi bi-trash3"></i> Удалить отвеченных
                                </button>
                            </div>
                            <div class="consultant-user-tabs" role="tablist" aria-label="Список чатов">
                                <button type="button" class="consultant-user-tab active" id="consultantChatsTab" role="tab" aria-selected="true" data-view="chats">
                                    Чаты <span id="consultantChatsCount">0</span>
                                </button>
                                <button type="button" class="consultant-user-tab" id="consultantArchiveTab" role="tab" aria-selected="false" data-view="archive">
                                    Архив <span id="consultantArchiveCount">0</span>
                                </button>
                            </div>
                            <div id="consultantUsers" class="consultant-users-list"></div>
                        </aside>

                        <section class="col-lg-8 consultant-thread-panel">
                            <div class="consultant-thread-header">
                                <strong id="consultantThreadTitle">Консультант</strong>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="consultantCloseThread" disabled>
                                    <i class="bi bi-x-circle"></i> Закрыть чат
                                </button>
                            </div>
                            <div id="consultantThread" class="consultant-thread"></div>
                            <form id="consultantReplyForm" class="consultant-reply-form">
                                <textarea id="consultantReplyInput" rows="3" placeholder="Напишите ответ посетителю..." maxlength="1000"></textarea>
                                <button type="submit" class="btn btn-orange fw-bold">Отправить ответ</button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
