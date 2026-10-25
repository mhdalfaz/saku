export function formatIDR(value: number | string | null | undefined): string {
    if (value === null || value === undefined) return "Rp 0";

    const numberString = value.toString().replace(/[^\d]/g, "");
    if (numberString === "") return "Rp 0";

    const number = parseInt(numberString, 10);
    return "Rp " + number.toLocaleString("id-ID");
}

export function unformatIDR(formatted: string): number {
    if (!formatted) return 0;
    return parseInt(formatted.replace(/[^\d]/g, ""), 10) || 0;
}
