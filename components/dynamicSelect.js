export async function populateSelect({
										 selector,
										 url,
										 valueKey,
										 textFormatter,
										 placeholder = '--Please Select--'
									 }) {
	const selects = document.querySelectorAll(selector);

	if (!selects.length) {
		return;
	}

	let data;

	try {
		const response = await fetch(url, {
			headers: {
				'Accept': 'application/json'
			}
		});

		if (!response.ok) {
			throw new Error(
				`Failed to load ${url}: ${response.status} ${response.statusText}`
			);
		}

		data = await response.json();

	} catch (error) {
		console.error(error);
		return;
	}

	selects.forEach(select => {
		// Keep the placeholder
		select.innerHTML = '';

		const placeholderOption = document.createElement('option');
		placeholderOption.value = '';
		placeholderOption.textContent = placeholder;

		select.appendChild(placeholderOption);

		data.forEach(item => {
			const option = document.createElement('option');

			option.value = item[valueKey];
			option.textContent = textFormatter(item);

			select.appendChild(option);
		});
	});
}
