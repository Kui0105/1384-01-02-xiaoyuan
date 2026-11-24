(function () {
    var path = (location.pathname || '').toLowerCase();
    if (path.indexOf('/login/') === 0) return;
    if (localStorage.getItem('doctor_demo_login') !== '1') {
        location.replace('/login/index.html');
    }
})();
