import {api} from './api.js';

const registerForm = document.querySelector('#register-form');
registerForm?.addEventListener('submit', function (event) {
    event.preventDefault();
    const name = document.querySelector('#name').value;
    const email = document.querySelector('#email').value;
    const password = document.querySelector('#password').value;
    const passwordConfirmation = document.querySelector('#password_confirmation').value;

    if (password !== passwordConfirmation) {
        return alert('Password and confirm password should match!!');
    }

    api('/api/register', 'POST', {
        name: name,
        email: email,
        password: password
    }).then(response => {
        if (response) {
            location.href = 'login.html?msg=registered';
        }
    })
})

const loginForm = document.querySelector('#login-form');
loginForm?.addEventListener('submit', function (event) {
    event.preventDefault();
    const email = document.querySelector('#email').value;
    const password = document.querySelector('#password').value;

    api('/api/login', 'POST', {
        email: email,
        password: password
    }).then(response => {
        if (response) {
            localStorage.setItem('access_token', response.data.token)
            location.href = 'index.html?msg=loggedin';
        }
    })

})