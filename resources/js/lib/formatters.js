const dateTimeFormatter = new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
});

export function formatDateTime(value) {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return dateTimeFormatter.format(date);
}

export function formatMoneyInput(value) {
    const sanitized = String(value)
        .replace(/,/g, '')
        .replace(/[^\d.]/g, '');

    const [integerPart, ...decimalParts] = sanitized.split('.');
    const decimals = decimalParts.join('').slice(0, 2);

    if (decimalParts.length === 0) {
        return integerPart;
    }

    return `${integerPart || '0'}.${decimals}`;
}

export function normalizeAmountForApi(value) {
    const formatted = formatMoneyInput(value).trim();

    if (formatted === '' || formatted === '0.') {
        return formatted;
    }

    return formatted.endsWith('.') ? formatted.slice(0, -1) : formatted;
}
