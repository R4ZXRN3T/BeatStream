export function initDynamicFields({
									  containerSelector,
									  fieldSelector,
									  removeButtonSelector,
									  addButtonSelector,
									  minFields = 1
								  }) {
	const container = document.querySelector(containerSelector);
	const addButton = document.querySelector(addButtonSelector);

	if (!container || !addButton) {
		console.warn('Dynamic fields: required elements not found');
		return;
	}

	function updateRemoveButtons() {
		const fields = container.querySelectorAll(fieldSelector);

		fields.forEach(field => {
			const button = field.querySelector(removeButtonSelector);

			if (button) {
				button.style.display =
					fields.length > minFields
						? 'inline-block'
						: 'none';
			}
		});
	}

	function addField() {
		const fields = container.querySelectorAll(fieldSelector);
		const template = fields[0];

		if (!template) {
			console.warn('Dynamic fields: no template field found');
			return;
		}

		const newField = template.cloneNode(true);

		// Reset inputs/selects in cloned field
		newField.querySelectorAll('input, select, textarea').forEach(element => {
			if (element.type === 'checkbox' || element.type === 'radio') {
				element.checked = false;
			} else {
				element.value = '';
			}
		});

		container.appendChild(newField);

		updateRemoveButtons();
	}

	function removeField(button) {
		const fields = container.querySelectorAll(fieldSelector);

		if (fields.length <= minFields) {
			return;
		}

		const field = button.closest(fieldSelector);

		if (field) {
			field.remove();
		}

		updateRemoveButtons();
	}

	addButton.addEventListener('click', addField);

	container.addEventListener('click', event => {
		const button = event.target.closest(removeButtonSelector);

		if (!button) {
			return;
		}

		removeField(button);
	});

	updateRemoveButtons();

	return {
		addField,
		removeField,
		updateRemoveButtons
	};
}
