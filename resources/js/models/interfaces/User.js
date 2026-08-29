import Roles from "../enums/Roles.ts";
export const defaultUser = {
    id: -1,
    username: "",
    email: undefined,
    role: Roles.Invalid
};
export function isAuthenticated(user) {
    return user !== null && user.role !== Roles.Invalid;
}
export function isRole(user, role) {
    return isAuthenticated(user) && user.role === role;
}
