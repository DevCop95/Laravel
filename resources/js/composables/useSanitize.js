export function useSanitize() {
    const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

    const sanitizeField = (form, field) => {
        form[field] = sanitizeRecordText(form[field] ?? '');
    };

    return { sanitizeRecordText, sanitizeField };
}
