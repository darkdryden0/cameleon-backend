document.addEventListener('DOMContentLoaded', function () {

})

function previousPage(page) {
    if (page < 1) {
        return;
    }
    const url = new URL(location);
    url.searchParams.set('page', page);
    location.href = url;
}

function nextPage(page) {
    const url = new URL(location);
    url.searchParams.set('page', page);
    location.href = url;
}

function changeLimit(element) {
    const url = new URL(location);
    url.searchParams.set('page', 1);
    url.searchParams.set('limit', element.value);
    location.href = url;
}