import Roles from "../enums/Roles.ts";
export default interface User {
    id: number;
    username: string;
    email?: string;
    role: Roles;
}

export const defaultUser: User = {
    id: -1,
    username: "",
    email: undefined,
    role: Roles.Invalid
}

export function isAuthenticated(user: User | null): user is User {
    return user !== null && user.role !== Roles.Invalid;
}

export function isRole(user: User | null, role: Roles): boolean {
    return isAuthenticated(user) && user.role === role;
}
