export function useFormatting() {
    const formatCurrency = (value) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0,
        }).format(Number(value || 0));

    const formatDate = (value) =>
        value
            ? new Date(value).toLocaleString('es-CO', {
                  day: '2-digit',
                  month: 'short',
                  year: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit',
              })
            : 'Sin fecha';

    const formatDateLong = (value) =>
        value
            ? new Date(value).toLocaleString('es-CO', {
                  day: '2-digit',
                  month: 'long',
                  year: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit',
              })
            : 'Pendiente';

    const statusBadgeClass = (status) => {
        if (status === 'completed') return 'bg-[#dce6d1] text-[#24543f]';
        if (status === 'cancelled') return 'bg-[#f3ded7] text-[#8a4d3a]';
        if (status === 'in_progress') return 'bg-[#dce7ef] text-[#36556e]';
        return 'bg-[#efe3c8] text-[#6e5b42]';
    };

    return { formatCurrency, formatDate, formatDateLong, statusBadgeClass };
}
