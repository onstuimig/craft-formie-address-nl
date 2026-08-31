export function debounce<T extends (...args: any[]) => void>(
	func: T,
	timeout: number,
)  {
	let timeoutId: ReturnType<typeof setTimeout> | null;

	return function <U>(this: U, ...args: Parameters<typeof func>) {
		const context = this;

		if (timeoutId) {
			clearTimeout(timeoutId);
		}

		timeoutId = setTimeout(() => {
			timeoutId = null;
			func.apply(context, args);
		}, timeout);
	};
}