export type User = {
    id: number;
    username?: string | null;
    nama?: string | null;
    email_bps?: string | null;
    email_gmail?: string | null;
    url_foto?: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
