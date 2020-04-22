export function prettyFileSize(size) {
    let units = ['TB', 'GB', 'MB', 'KB'];
    let unit = 'Bytes';
    while (size >= 1024 && units.length) {
        size /= 1024;
        unit = units.pop();
    };
    return `${Math.floor(100 * size) / 100} ${unit}`;
}

export function percentage(value, decimal = 2) {
    return (value * 100).toFixed(decimal) + '%';
}

export function formatTimestamp(timestamp) {
    let date = new Date(timestamp * 1000);
    return ''
        + date.getFullYear() + '-'
        + (date.getMonth() + 1).toString().padStart(2, 0) + '-'
        + date.getDate().toString().padStart(2, 0) + ' '
        + date.getHours().toString().padStart(2, 0) + ':'
        + date.getMinutes().toString().padStart(2, 0) + ':'
        + date.getSeconds().toString().padStart(2, 0);
};