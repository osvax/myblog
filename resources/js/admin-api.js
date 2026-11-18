// ============================================================
// API админ-панели: JSON-запросы с CSRF-токеном
// Порт public/admin/js/admin-api.js
// ============================================================

function csrfToken() {
    return fetch('/api/auth/csrf', { credentials: 'same-origin' }).then(function (response) {
        return response.json().then(function (data) {
            if (!response.ok || !data.success || !data.token) {
                throw new Error(data.error || 'Не удалось получить токен защищённого запроса.');
            }
            return data.token;
        });
    });
}

function request(path, options) {
    options = options || {};
    const method = (options.method || 'GET').toUpperCase();
    const headers = Object.assign({}, options.headers || {});
    let body = options.body;
    const tokenPromise = method === 'GET' || method === 'HEAD'
        ? Promise.resolve(null)
        : csrfToken();

    return tokenPromise.then(function (token) {
        if (token) headers['X-CSRF-Token'] = token;
        if (body && !(body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }
        return fetch(path, {
            method: method,
            credentials: 'same-origin',
            headers: headers,
            body: body
        });
    }).then(function (response) {
        if (response.status === 401) {
            window.location.href = '/admin/login.html';
            throw new Error('Войдите в административную панель заново.');
        }
        if (response.status === 204) return null;
        const contentType = response.headers.get('Content-Type') || '';
        if (!contentType.includes('application/json')) {
            if (!response.ok) throw new Error('Сервер отклонил запрос (' + response.status + ').');
            return response;
        }
        return response.json().then(function (data) {
            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Не удалось выполнить запрос.');
            }
            return data;
        });
    });
}

export const AdminApi = {
    request: request,
    get: function (path) { return request(path); },
    // Метод post был пропущен в легаси, хотя используется users.js и roles.js
    post: function (path, data) { return request(path, { method: 'POST', body: data }); },
    save: function (path, method, data) { return request(path, { method: method, body: data }); },
    remove: function (path) { return request(path, { method: 'DELETE' }); }
};

export default AdminApi;
