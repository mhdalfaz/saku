import axios, { AxiosError, type InternalAxiosRequestConfig } from "axios";

const TOKEN_KEY = "token";

export const getToken = () => localStorage.getItem(TOKEN_KEY);
export const setToken = (token: string) => localStorage.setItem(TOKEN_KEY, token);
export const clearToken = () => localStorage.removeItem(TOKEN_KEY);

export const api = axios.create({
    baseURL: "/api",
    headers: { Accept: "application/json" },
});

api.interceptors.request.use((config) => {
    const token = getToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

let refreshing: Promise<string | null> | null = null;

async function refresh(): Promise<string | null> {
    try {
        const res = await api.post("/refresh");
        const token = res.data?.data?.token;
        if (token) {
            setToken(token);
            return token;
        }
        return null;
    } catch {
        return null;
    } finally {
        refreshing = null;
    }
}

type RetriableConfig = InternalAxiosRequestConfig & { _retry?: boolean };

api.interceptors.response.use(
    (response) => response,
    async (error: AxiosError) => {
        const original = error.config as RetriableConfig | undefined;
        const status = error.response?.status;
        const url = original?.url ?? "";
        const canRefresh =
            status === 401 &&
            original &&
            !original._retry &&
            !!getToken() &&
            !url.includes("/refresh") &&
            !url.includes("/login");

        if (canRefresh) {
            original!._retry = true;
            const token = await (refreshing ??= refresh());
            if (token) {
                original!.headers.Authorization = `Bearer ${token}`;
                return api(original!);
            }
            alert("Sesi Anda telah berakhir, silakan login kembali.");
            clearToken();
            window.location.href = "/login";
        }

        return Promise.reject(error);
    },
);
