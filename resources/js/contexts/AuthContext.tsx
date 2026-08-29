import {createContext, useContext, useEffect, useState} from 'react';
import User, {defaultUser} from "../models/interfaces/User.ts";
import api from "../lib/axios.ts";

const AuthContext = createContext({
    user: defaultUser,
    refreshAuth: async () => {
    },
});

export const AuthProvider = ({children}: any) => {
    const [user, setUser] = useState<User>(defaultUser)

    useEffect(() => {
        checkAuth()
    }, []);

    async function checkAuth() {
        return await api.get("/api/v1/user")
            .then((response) => setUser(response.data))
            .catch(() => setUser(defaultUser));
    }

    return (
        <AuthContext.Provider
            value={{
                user: user,
                refreshAuth: checkAuth
            }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
