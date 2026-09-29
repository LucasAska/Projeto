const toggle = document.getElementById('menuToggle');
const menu = document.getElementById('menuPrincipal');
if (toggle && menu) toggle.addEventListener('click', () => menu.classList.toggle('aberto'));

document.querySelectorAll('[data-confirmar]').forEach(el => {
    el.addEventListener('click', ev => {
        if (!confirm(el.dataset.confirmar)) ev.preventDefault();
    });
});

const inputImagem = document.getElementById('inputImagem');
const preview = document.getElementById('previewImagem');
if (inputImagem && preview) {
    inputImagem.addEventListener('change', () => {
        const arquivo = inputImagem.files[0];
        if (arquivo) {
            preview.src = URL.createObjectURL(arquivo);
            preview.style.display = 'block';
        }
    });
}

setTimeout(() => {
    document.querySelectorAll('.alerta').forEach(a => {
        a.style.transition = 'opacity .5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 6000);