const form = document.getElementById('loginForm');

if (form) {
    const button = document.getElementById('submitBtn');
    const text = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');
    const icon = document.getElementById('btnIcon');
    const error = document.getElementById('errorContainer');
    const message = document.getElementById('errorMessage');
    let submitting = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        text.textContent = 'Connexion en cours…';
        spinner.hidden = false;
        icon.hidden = true;
        error.hidden = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: new FormData(form),
            });

            if (response.ok && !response.redirected) {
                window.location.assign(form.dataset.redirect);
                return;
            }

            if (response.status === 429) {
                const seconds = Number(response.headers.get('Retry-After')) || 60;
                message.textContent = `Trop de tentatives. Réessayez dans ${seconds} secondes.`;
            } else if (response.status === 419) {
                message.textContent = 'Votre session a expiré. Rechargez la page puis réessayez.';
            } else if (response.status === 422) {
                const data = await response.json();
                message.textContent = Object.values(data.errors || {})[0]?.[0] || 'Identifiant ou mot de passe incorrect.';
            } else {
                message.textContent = 'Connexion indisponible. Veuillez réessayer.';
            }
            error.hidden = false;
        } catch {
            message.textContent = 'Impossible de joindre le serveur. Vérifiez votre connexion.';
            error.hidden = false;
        } finally {
            submitting = false;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            text.textContent = 'Se connecter';
            spinner.hidden = true;
            icon.hidden = false;
        }
    });
}
