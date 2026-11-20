(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('clientForm');
        var errorBox = document.getElementById('clientError');
        var query = new URLSearchParams(window.location.search);
        var orderId = Number(query.get('id'));
        var order = null;
        var fields = Array.from(form.elements).filter(function (field) {
            return field.name;
        });
        var briefModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('briefModal'));

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function fillForm(data) {
            fields.forEach(function (field) {
                if (Object.prototype.hasOwnProperty.call(data, field.name)) {
                    field.value = data[field.name] === null ? '' : data[field.name];
                }
            });
            // Заголовок карточки (clientNameHeading) обновляется при вводе в поле ФИО
            document.getElementById('clientNameHeading').textContent = data.clientName || 'Данные клиента';
            document.getElementById('orderNumber').textContent = 'Заказ №' + order.id + ' · создан ' + formatDate(data.orderDate);
            document.getElementById('clientStatusBadge').textContent = data.status;
            updatePayment();
            updateCancelReason();
        }

        function formatDate(value) {
            if (!value) return '—';
            var parts = value.split('-');
            return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : value;
        }

        function currentFormData() {
            var data = {};
            fields.forEach(function (field) {
                data[field.name] = field.value.trim();
            });
            data.budget = Number(data.budget || 0);
            data.paidAmount = Number(data.paidAmount || 0);
            return data;
        }

        function updatePayment() {
            var total = Number(document.getElementById('projectBudget').value || 0);
            var paid = Number(document.getElementById('paidAmount').value || 0);
            var balance = total - paid;
            var balanceElement = document.getElementById('projectBalance');
            var stateElement = document.getElementById('paymentState');
            balanceElement.textContent = Math.abs(balance).toLocaleString('ru-RU') + ' ₽';
            balanceElement.className = balance > 0 ? 'text-danger' : 'text-success';
            stateElement.textContent = balance > 0 ? 'Есть задолженность' :
                balance < 0 ? 'Переплата' : 'Оплачено полностью';
            stateElement.className = 'small fw-bold ' + (balance > 0 ? 'text-danger' : 'text-success');
        }

        function updateCancelReason() {
            var canceled = document.getElementById('projectStatus').value === 'Отменён';
            document.getElementById('cancelReasonGroup').classList.toggle('d-none', !canceled);
            document.getElementById('clientStatusBadge').textContent = document.getElementById('projectStatus').value;
        }

        function renderBriefPreview() {
            var data = currentFormData();
            document.getElementById('briefModalTitle').textContent = 'Техническое задание — ' +
                (data.projectTitle || 'Без названия');
            document.getElementById('briefPreviewText').textContent =
                data.technicalBrief || 'Техническое задание пока не заполнено.';
            briefModal.show();
        }

        function printBrief() {
            var data = currentFormData();
            var printWindow = window.open('', '_blank');
            if (!printWindow) {
                window.AdminNotifications.show('error', 'Разрешите всплывающие окна для печати ТЗ в PDF');
                return;
            }
            var brief = data.technicalBrief || 'Техническое задание пока не заполнено.';
            printWindow.addEventListener('load', function () {
                printWindow.focus();
                printWindow.print();
            });
            printWindow.document.open();
            printWindow.document.write('<!doctype html><html lang="ru"><head><meta charset="utf-8">' +
                '<title>Техническое задание — ' + escapeHtml(data.projectTitle || 'Проект') + '</title>' +
                '<style>@page{size:A4;margin:20mm}body{font:14px/1.6 Arial,sans-serif;color:#222}' +
                'h1{font-size:22px;border-bottom:2px solid #ff8c1a;padding-bottom:10px}' +
                'h2{font-size:16px;margin-top:24px}pre{white-space:pre-wrap;overflow-wrap:anywhere;' +
                'font:14px/1.6 Arial,sans-serif}.meta{color:#555;margin:4px 0}</style></head><body>' +
                '<h1>Техническое задание</h1><h2>' + escapeHtml(data.projectTitle || 'Проект без названия') +
                '</h2><p class="meta">Клиент: ' + escapeHtml(data.clientName || '—') + '</p>' +
                '<p class="meta">Тип проекта: ' + escapeHtml(data.projectType || '—') + '</p>' +
                '<p class="meta">Дата заказа: ' + escapeHtml(formatDate(data.orderDate)) + '</p>' +
                '<hr><pre>' + escapeHtml(brief) + '</pre></body></html>');
            printWindow.document.close();
        }

        function saveForm(event) {
            event.preventDefault();
            errorBox.hidden = true;
            if (!form.reportValidity()) return;

            var data = currentFormData();
            if (data.paidAmount < 0 || data.budget < 0) {
                errorBox.textContent = 'Стоимость и сумма оплат не могут быть отрицательными.';
                errorBox.hidden = false;
                return;
            }
            if (data.completedAt && data.completedAt < data.orderDate) {
                errorBox.textContent = 'Дата выполнения не может быть раньше даты заказа.';
                errorBox.hidden = false;
                return;
            }
            if (data.deadline && data.deadline < data.orderDate) {
                errorBox.textContent = 'Плановая дата завершения не может быть раньше даты заказа.';
                errorBox.hidden = false;
                return;
            }
            try {
                window.AdminApi.save('/api/admin/orders/' + encodeURIComponent(orderId), 'PUT', data)
                    .then(function (result) {
                        order = result.order;
                        window.dispatchEvent(new Event('admin-orders-state-changed'));
                        fillForm(order);
                        window.AdminNotifications.show('success', 'Карточка клиента сохранена');
                    }).catch(function (error) {
                        console.error('Не удалось сохранить карточку клиента.', error);
                        errorBox.textContent = error.message || 'Не удалось сохранить данные заказа.';
                        errorBox.hidden = false;
                        window.AdminNotifications.show('error', errorBox.textContent);
                    });
            } catch (error) {
                console.error('Не удалось подготовить карточку клиента.', error);
                errorBox.textContent = error.message || 'Не удалось подготовить данные заказа.';
                errorBox.hidden = false;
                window.AdminNotifications.show('error', errorBox.textContent);
            }
        }

        document.getElementById('clientName').addEventListener('input', function () {
            document.getElementById('clientNameHeading').textContent = this.value || 'Данные клиента';
        });
        document.getElementById('projectBudget').addEventListener('input', updatePayment);
        document.getElementById('paidAmount').addEventListener('input', updatePayment);
        document.getElementById('projectStatus').addEventListener('change', updateCancelReason);
        document.getElementById('previewBrief').addEventListener('click', renderBriefPreview);
        document.getElementById('downloadBrief').addEventListener('click', printBrief);
        document.getElementById('printBriefFromModal').addEventListener('click', printBrief);
        form.addEventListener('submit', saveForm);

        if (!Number.isInteger(orderId) || orderId < 1) {
            errorBox.textContent = 'В ссылке указан некорректный номер заказа.';
            errorBox.hidden = false;
            form.hidden = true;
            return;
        }

        window.AdminApi.get('/api/admin/orders').then(function (result) {
            order = result.orders.find(function (item) { return item.id === orderId; });
            if (!order) throw new Error('Заказ не найден или удалён');
            fillForm(order);
        }).catch(function (error) {
            errorBox.textContent = error.message || 'Не удалось загрузить карточку клиента';
            errorBox.hidden = false;
            form.hidden = true;
            window.AdminNotifications.show('error', errorBox.textContent);
        });
    });
}());
