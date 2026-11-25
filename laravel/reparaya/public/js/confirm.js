// Confirmación genérica: cualquier <form data-confirm="mensaje"> pide confirmación antes de enviarse.
// Se carga al final del layout (@stack('scripts')).
document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !confirm(message)) {
        event.preventDefault();
    }
});
