document.addEventListener('DOMContentLoaded', () => {

    const registerButton = document.getElementById('register');
    const loginButton = document.getElementById('login');
    const container = document.getElementById('container');

    registerButton.addEventListener('click', () => {
        container.classList.remove('close');
        container.classList.add('active');
    });

    loginButton.addEventListener('click', () => {
        container.classList.remove('active');
        container.classList.add('close');
    });

});