import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
    type ReactNode,
} from "react";
import { api, clearToken, getToken, setToken } from "@/lib/api";
import type { User } from "@/lib/types";

interface AuthContextValue {
    user: User | null;
    loading: boolean;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            if (!getToken()) {
                setLoading(false);
                return;
            }
            try {
                const res = await api.get("/me");
                setUser(res.data.data as User);
            } catch {
                // interceptor menangani refresh / redirect
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    const login = useCallback(async (email: string, password: string) => {
        const res = await api.post("/login", { email, password });
        setToken(res.data.data.token as string);
        const me = await api.get("/me");
        setUser(me.data.data as User);
    }, []);

    const logout = useCallback(async () => {
        try {
            await api.post("/logout");
        } catch {
            // token mungkin sudah tidak valid
        }
        clearToken();
        setUser(null);
    }, []);

    return (
        <AuthContext.Provider value={{ user, loading, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth(): AuthContextValue {
    const ctx = useContext(AuthContext);
    if (!ctx) {
        throw new Error("useAuth harus dipakai di dalam AuthProvider");
    }
    return ctx;
}
