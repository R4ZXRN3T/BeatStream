export async function initForm({
								   selector,          								// CSS selector for the <form>
								   spinnerSelector = null,   				// optional: overlay element that shows while loading
								   toastContainerId = 'toast-container', 	// default container for Bootstrap toasts
								   onSuccess = () => {
								   },   						// callback after a 2xx response
								   onError = () => {
								   }      						// callback after non‑2xx or network error
							   }) {

	const form = document.querySelector(selector);
	if (!form) return;

	const spinner = spinnerSelector ? document.querySelector(spinnerSelector) : null;
	const toastContainer = document.getElementById(toastContainerId);
	if (!toastContainer) {
		console.error('Toast container not found!');
		return;
	}

	/* ---- helper: show a Bootstrap toast ---- */
	function showToast(message, type = 'success') {
		const id = `toast-${Date.now()}`;
		const div = document.createElement('div');
		div.id = id;
		div.className = `toast align-items-center text-bg-${type} border-0 mb-2`;
		div.setAttribute('role', 'alert');
		div.setAttribute('aria-live', 'assertive');
		div.setAttribute('aria-atomic', 'true');

		div.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
		toastContainer.appendChild(div);

		const bsToast = new bootstrap.Toast(div, {delay: 4000});
		bsToast.show();

		div.addEventListener('hidden.bs.toast', () => div.remove());
	}

	/* ---- form submit handler ---- */
	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		if (spinner) spinner.classList.remove('d-none');

		const fd = new FormData(form);
		try {
			const resp = await fetch(form.action, {
				method: 'POST',
				body: fd,
				credentials: 'include'
			});

			const json = await resp.json();

			if (!resp.ok) {
				showToast(json.error || json.message || 'Error', 'danger');
				onError(json);
			} else {
				showToast('Success!', 'success');
				form.reset();
				onSuccess(json);
			}
		} catch (err) {
			console.warn(err);
			showToast('Network error – please try again.', 'danger');
			onError(err);
		} finally {
			if (spinner) spinner.classList.add('d-none');
		}
	});
}
