import Roles from "../enums/Roles.ts";
export default interface User {
    id: number;
    username: string;
    email?: string;
    email_verified_at: string | null;
    role: Roles;
}

export const defaultUser: User = {
    id: -1,
    username: "",
    email: undefined,
    email_verified_at: null,
    role: Roles.Invalid
}

export function isAuthenticated(user: User | null): user is User {
    return user !== null && user.role !== Roles.Invalid;
}

export function isRole(user: User | null, role: Roles): boolean {
    return isAuthenticated(user) && user.role === role;
}
