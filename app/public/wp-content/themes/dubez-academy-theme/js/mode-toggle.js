document.addEventListener('DOMContentLoaded', function(){

    const root = document.documentElement;
    const toggleButtons = document.querySelectorAll('.mode-toggle');

    let mode = localStorage.getItem('mode') || 'light';

    root.classList.remove('dark-mode','light-mode');
    root.classList.add(mode + '-mode');

    toggleButtons.forEach(btn => {

        btn.innerHTML = mode === 'dark' ? '☀️' : '🌙';

        btn.addEventListener('click', function(){

            root.classList.toggle('dark-mode');
            root.classList.toggle('light-mode');

            mode = root.classList.contains('dark-mode') ? 'dark' : 'light';
            localStorage.setItem('mode', mode);

            btn.innerHTML = mode === 'dark' ? '☀️' : '🌙';
        });

    });

});