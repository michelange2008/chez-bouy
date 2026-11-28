const months = document.querySelector('[data-availability-months]');
const nextMonthLink = document.querySelector('[data-availability-next]');

if (months && nextMonthLink && 'IntersectionObserver' in window) {
    const error = document.querySelector('[data-availability-error]');

    const observer = new IntersectionObserver(async ([entry]) => {
        if (!entry.isIntersecting) {
            return;
        }

        observer.unobserve(nextMonthLink);
        try {
            const response = await fetch(nextMonthLink.href);
            if (!response.ok) {
                throw new Error('Availability request failed');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const month = page.querySelector('[data-availability-month]');
            const followingMonth = page.querySelector('[data-availability-next]');
            if (!month || !followingMonth) {
                throw new Error('Availability month missing');
            }

            months.append(month);
            nextMonthLink.href = followingMonth.href;
            observer.observe(nextMonthLink);
        } catch {
            error.hidden = false;
        }
    }, { rootMargin: '0px 0px 160px 0px' });

    observer.observe(nextMonthLink);
}
