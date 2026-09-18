export function tripDays(departureDate, returnDate) {
    if (!departureDate || !returnDate) {
        return null;
    }

    const departure = new Date(`${departureDate}T00:00:00`);
    const returnDay = new Date(`${returnDate}T00:00:00`);
    const diff = Math.round((returnDay - departure) / (1000 * 60 * 60 * 24)) + 1;

    return diff >= 1 ? diff : null;
}

export function formatShortDate(value) {
    return new Date(`${value}T00:00:00`).toLocaleDateString('es', { day: 'numeric', month: 'short' });
}

export function todayIso() {
    return new Date().toISOString().slice(0, 10);
}

export function addDaysIso(dateIso, days) {
    const date = new Date(`${dateIso}T00:00:00`);
    date.setDate(date.getDate() + days);

    return date.toISOString().slice(0, 10);
}
