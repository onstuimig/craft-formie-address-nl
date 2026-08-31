import { debounce } from "./utilities/Helpers";

class FormieAddressNLAutocomplete {
	private $form: HTMLFormElement;
	private form: any;
	private $field: HTMLElement;

	private actionUrl: string;

	private postalCodeInput!: HTMLInputElement;
	private houseNumberInput!: HTMLInputElement;
	private houseNumberAdditionInput!: HTMLInputElement | null;
	private streetInput!: HTMLInputElement | null;
	private cityInput!: HTMLInputElement | null;
	private provinceInput!: HTMLInputElement | null;
	private countryInput!: HTMLInputElement | null;

	private resultElement!: HTMLDivElement | null;

	private abortController: AbortController | null = null;

	constructor(settings: {
		$form: HTMLFormElement;
		$field: HTMLElement;
		actionUrl: string;
		[key: string]: any;
	}) {
		this.$form = settings.$form;
        this.form = this.$form.form;
        this.$field = settings.$field;

		this.actionUrl = settings.actionUrl;
		
		if(!this.$field || !(this.$field.dataset.fieldType === 'address-nl')) return;

		const postalCodeInput = this.$field.querySelector('[data-field-type="address-nl-postal-code"] input') as HTMLInputElement | null;
		const houseNumberInput = this.$field.querySelector('[data-field-type="address-nl-house-number"] input') as HTMLInputElement | null;
		const houseNumberAdditionInput = this.$field.querySelector('[data-field-type="address-nl-house-number-addition"] input') as HTMLInputElement | null;
		
		if(!postalCodeInput || !houseNumberInput) return;

		this.postalCodeInput = postalCodeInput;
		this.houseNumberInput = houseNumberInput;
		this.houseNumberAdditionInput = houseNumberAdditionInput;

		this.streetInput = this.$field.querySelector('[data-field-type="address-nl-street"] input') as HTMLInputElement | null;
		this.cityInput = this.$field.querySelector('[data-field-type="address-nl-city"] input') as HTMLInputElement | null;
		this.provinceInput = this.$field.querySelector('[data-field-type="address-nl-province"] input') as HTMLInputElement | null;
		this.countryInput = this.$field.querySelector('[data-field-type="address-nl-country"] input') as HTMLInputElement | null;

		this.resultElement = this.$field.querySelector('[data-field-type="address-nl-autocomplete"] [data-address-nl-autocomplete-result]') as HTMLDivElement | null;

		this.initAutocomplete();
	}

	public initAutocomplete() {
		if(!this.actionUrl) return;

		this.setExtendedInputFieldVisiblility(false);
		this.setResultVisibility(false);

		const triggerInputs = [
			this.postalCodeInput,
			this.houseNumberInput,
			this.houseNumberAdditionInput
		];

		triggerInputs.forEach(input => {
			input?.addEventListener('input', () => {
				this.getAddress();
			});
		});
	}

	public setExtendedInputFieldVisiblility(visible: boolean) {
		[
			this.streetInput,
			this.cityInput,
			this.provinceInput,
			this.countryInput
		].forEach(input => {
			if(!input) return;

			const $field = input.closest('[data-field-type]') as HTMLElement | null;
			if($field) {
				if(visible) {
					$field.removeAttribute('data-autocomplete-hidden');
				} else {
					$field.setAttribute('data-autocomplete-hidden', 'true');
				}
				this.updateRowVisibility($field);
			}
		});
	}

	public setResultVisibility(visible: boolean) {
		if(!this.resultElement) return;

		const $field = this.resultElement.closest('[data-field-type]') as HTMLElement | null;
		
		if(!$field) return;

		if(visible) {
			$field.removeAttribute('data-autocomplete-hidden');
		} else {
			$field.setAttribute('data-autocomplete-hidden', 'true');
		}
		this.updateRowVisibility($field);
	}

	public getAddress = debounce(this._getAddress, 200);
	private async _getAddress() {
		if (this.abortController) {
			this.abortController.abort();
		}
		this.abortController = new AbortController();

		const postalCode = this.postalCodeInput.value;
		const houseNumber = this.houseNumberInput.value;
		const houseNumberAddition = this.houseNumberAdditionInput?.value ?? '';

		if (postalCode == '' || houseNumber == '') {
			return;
		}

		// Emit an "onBeforeAddressNLAutocomplete" event
		this.$field.dispatchEvent(new CustomEvent('onBeforeAddressNLAutocomplete', {
			bubbles: true,
			detail: {
				field: this.$field
			}
		}));

		const actionUrl = new URL(this.actionUrl);
		actionUrl.searchParams.append('postalCode', postalCode);
		actionUrl.searchParams.append('houseNumber', houseNumber);
		if(houseNumberAddition != '') {
			actionUrl.searchParams.append('houseNumberAddition', houseNumberAddition);
		}

		let data: {
			street: string | null;
			houseNumber: string | null;
			houseNumberAddition: string | null;
			postalCode: string | null;
			city: string | null;
			province: string | null;
			country: string | null;
		} | null = null;
		try {
			const response = await fetch(actionUrl.toString(), {
				signal: this.abortController.signal,
				headers: {
					'Content-Type': 'application/json'
				},
			});
			data = await response.json();
			
		} catch (error) {
			if(!(error instanceof DOMException) || error.name !== 'AbortError') {
				console.error(error);
			}
		}
		
		if (data && data.street) {
			if(this.streetInput) this.streetInput.value = data.street;
			if(this.cityInput) this.cityInput.value = data.city ?? '';
			if(this.provinceInput) this.provinceInput.value = data.province ?? '';
			if(this.countryInput) this.countryInput.value = data.country ?? '';

			if (this.resultElement) {
				let resultContent = [this.streetInput?.value ?? '', this.cityInput?.value ?? ''].filter(Boolean).join(', ');
				if(this.provinceInput || this.countryInput) {
					resultContent += '<br>'+[this.provinceInput?.value ?? '', this.countryInput?.value ?? ''].filter(Boolean).join(', ');
				}
				this.resultElement.innerHTML = (this.streetInput?.value ?? '') + ', '  + (this.cityInput?.value ?? '');
			}

			// Hide inputs, show result
			this.setExtendedInputFieldVisiblility(false);
			this.setResultVisibility(true);
		} else {
			if(this.streetInput) this.streetInput.value = '';
			if(this.cityInput) this.cityInput.value = '';
			if(this.provinceInput) this.provinceInput.value = '';
			if(this.countryInput) this.countryInput.value = '';

			if (this.resultElement) this.resultElement.innerHTML = '';

			// Show inputs, hide result
			this.setExtendedInputFieldVisiblility(true);
			this.setResultVisibility(false);
		}

		// Emit an "onAfterAddressNLAutocomplete" event
		this.$field.dispatchEvent(new CustomEvent('onAfterAddressNLAutocomplete', {
			bubbles: true,
			detail: {
				field: this.$field,
				address: data
			}
		}));
	}

	public updateRowVisibility($field: HTMLElement) {
		const $parent = $field.closest('[data-fui-field-count]');

		if ($parent) {
			const allFields = $parent.querySelectorAll('[data-field-handle]:not([data-conditionally-hidden]):not([data-autocomplete-hidden])');

			// Ensure that we're only checking on the first "level" of fields. For isntance, a Group field itself
			// might be conditionally hidden, but their inner fields won't be, producing incorrect results.
			// https://github.com/verbb/formie/issues/2337
			const $fields = Array.from(allFields).filter((el) => {
				return el.closest('[data-fui-field-count]') === $parent;
			});

			$parent.setAttribute('data-fui-field-count', $fields.length.toString());

			// Update the class if we have classes enabled
			if ($parent.classList.contains('fui-row')) {
				if ($fields.length === 0) {
					$parent.classList.add('fui-row-empty');
				} else {
					$parent.classList.remove('fui-row-empty');
				}
			}
		}
	}
}

// @ts-ignore
window.FormieAddressNLAutocomplete = FormieAddressNLAutocomplete;
