export interface AuthUser {
    name: string;
    email: string;
}

export interface SharedProps {
    auth: { user: AuthUser | null };
    flash: { status: string | null };
}
