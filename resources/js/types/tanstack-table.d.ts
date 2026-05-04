import '@tanstack/vue-table';

declare module '@tanstack/vue-table' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface ColumnMeta<TData, TValue> {
        hideOnMobile?: boolean;
        headerClass?: string;
        cellClass?: string;
    }
}
