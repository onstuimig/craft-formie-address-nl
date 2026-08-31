//#region src/js/utilities/Helpers.ts
function e(e, t) {
	let n;
	return function(...r) {
		let i = this;
		n && clearTimeout(n), n = setTimeout(() => {
			n = null, e.apply(i, r);
		}, t);
	};
}
window.FormieAddressNLAutocomplete = class {
	$form;
	form;
	$field;
	actionUrl;
	postalCodeInput;
	houseNumberInput;
	houseNumberAdditionInput;
	streetInput;
	cityInput;
	provinceInput;
	countryInput;
	resultElement;
	abortController = null;
	constructor(e) {
		if (this.$form = e.$form, this.form = this.$form.form, this.$field = e.$field, this.actionUrl = e.actionUrl, !this.$field || this.$field.dataset.fieldType !== "address-nl") return;
		let t = this.$field.querySelector("[data-field-type=\"address-nl-postal-code\"] input"), n = this.$field.querySelector("[data-field-type=\"address-nl-house-number\"] input"), r = this.$field.querySelector("[data-field-type=\"address-nl-house-number-addition\"] input");
		!t || !n || (this.postalCodeInput = t, this.houseNumberInput = n, this.houseNumberAdditionInput = r, this.streetInput = this.$field.querySelector("[data-field-type=\"address-nl-street\"] input"), this.cityInput = this.$field.querySelector("[data-field-type=\"address-nl-city\"] input"), this.provinceInput = this.$field.querySelector("[data-field-type=\"address-nl-province\"] input"), this.countryInput = this.$field.querySelector("[data-field-type=\"address-nl-country\"] input"), this.resultElement = this.$field.querySelector("[data-field-type=\"address-nl-autocomplete\"] [data-address-nl-autocomplete-result]"), this.initAutocomplete());
	}
	initAutocomplete() {
		this.actionUrl && (this.setExtendedInputFieldVisiblility(!1), this.setResultVisibility(!1), [
			this.postalCodeInput,
			this.houseNumberInput,
			this.houseNumberAdditionInput
		].forEach((e) => {
			e?.addEventListener("input", () => {
				this.getAddress();
			});
		}));
	}
	setExtendedInputFieldVisiblility(e) {
		[
			this.streetInput,
			this.cityInput,
			this.provinceInput,
			this.countryInput
		].forEach((t) => {
			if (!t) return;
			let n = t.closest("[data-field-type]");
			n && (e ? n.removeAttribute("data-autocomplete-hidden") : n.setAttribute("data-autocomplete-hidden", "true"), this.updateRowVisibility(n));
		});
	}
	setResultVisibility(e) {
		if (!this.resultElement) return;
		let t = this.resultElement.closest("[data-field-type]");
		t && (e ? t.removeAttribute("data-autocomplete-hidden") : t.setAttribute("data-autocomplete-hidden", "true"), this.updateRowVisibility(t));
	}
	getAddress = e(this._getAddress, 200);
	async _getAddress() {
		this.abortController && this.abortController.abort(), this.abortController = new AbortController();
		let e = this.postalCodeInput.value, t = this.houseNumberInput.value, n = this.houseNumberAdditionInput?.value ?? "";
		if (e == "" || t == "") return;
		this.$field.dispatchEvent(new CustomEvent("onBeforeAddressNLAutocomplete", {
			bubbles: !0,
			detail: { field: this.$field }
		}));
		let r = new URL(this.actionUrl);
		r.searchParams.append("postalCode", e), r.searchParams.append("houseNumber", t), n != "" && r.searchParams.append("houseNumberAddition", n);
		let i = null;
		try {
			i = await (await fetch(r.toString(), {
				signal: this.abortController.signal,
				headers: { "Content-Type": "application/json" }
			})).json();
		} catch (e) {
			(!(e instanceof DOMException) || e.name !== "AbortError") && console.error(e);
		}
		if (i && i.street) {
			if (this.streetInput && (this.streetInput.value = i.street), this.cityInput && (this.cityInput.value = i.city ?? ""), this.provinceInput && (this.provinceInput.value = i.province ?? ""), this.countryInput && (this.countryInput.value = i.country ?? ""), this.resultElement) {
				let e = [this.streetInput?.value ?? "", this.cityInput?.value ?? ""].filter(Boolean).join(", ");
				(this.provinceInput || this.countryInput) && (e += "<br>" + [this.provinceInput?.value ?? "", this.countryInput?.value ?? ""].filter(Boolean).join(", ")), this.resultElement.innerHTML = (this.streetInput?.value ?? "") + ", " + (this.cityInput?.value ?? "");
			}
			this.setExtendedInputFieldVisiblility(!1), this.setResultVisibility(!0);
		} else this.streetInput && (this.streetInput.value = ""), this.cityInput && (this.cityInput.value = ""), this.provinceInput && (this.provinceInput.value = ""), this.countryInput && (this.countryInput.value = ""), this.resultElement && (this.resultElement.innerHTML = ""), this.setExtendedInputFieldVisiblility(!0), this.setResultVisibility(!1);
		this.$field.dispatchEvent(new CustomEvent("onAfterAddressNLAutocomplete", {
			bubbles: !0,
			detail: {
				field: this.$field,
				address: i
			}
		}));
	}
	updateRowVisibility(e) {
		let t = e.closest("[data-fui-field-count]");
		if (t) {
			let e = t.querySelectorAll("[data-field-handle]:not([data-conditionally-hidden]):not([data-autocomplete-hidden])"), n = Array.from(e).filter((e) => e.closest("[data-fui-field-count]") === t);
			t.setAttribute("data-fui-field-count", n.length.toString()), t.classList.contains("fui-row") && (n.length === 0 ? t.classList.add("fui-row-empty") : t.classList.remove("fui-row-empty"));
		}
	}
};
//#endregion
