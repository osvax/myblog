(function () {
    'use strict';

    var searchInput = document.getElementById('blogSearchInput');
    var searchForm = document.getElementById('blogSearchForm');
    var searchInfo = document.getElementById('blogSearchInfo');
    var posts = document.querySelectorAll('#blogPosts .post-card');
    var noResults = document.getElementById('noResults');
    var pagination = document.querySelector('.pagination');

    if (!searchInput || !searchForm || !searchInfo || !noResults) {
        return;
    }

    function filterPosts() {
        var query = searchInput.value.trim().toLowerCase();
        var visible = 0;

        posts.forEach(function (post) {
            var matches = post.textContent.toLowerCase().includes(query);
            post.closest('.col-md-4').style.display = matches ? '' : 'none';
            if (matches) visible++;
        });

        noResults.classList.toggle('d-none', !query || visible > 0);
        if (pagination) pagination.classList.toggle('d-none', !!query && visible === 0);
        searchInfo.textContent = query && visible > 0 ? 'Найдено статей: ' + visible : '';
    }

    searchInput.addEventListener('input', filterPosts);
    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        filterPosts();
    });
    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            searchInput.value = '';
            filterPosts();
        }
    });
}());
