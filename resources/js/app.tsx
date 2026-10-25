import "@vitejs/plugin-react/preamble";
import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Navigate, Outlet, Route, Routes } from "react-router-dom";
import { AuthProvider, useAuth } from "@/auth/AuthContext";
import { AuthLayout, AppLayout, MenuLayout } from "@/components/layout/Layouts";
import { getToken } from "@/lib/api";
import Choose from "@/pages/Choose";
import Home from "@/pages/Home";
import Login from "@/pages/Login";
import Settings from "@/pages/Settings";
import LoanCreate from "@/pages/loans/LoanCreate";
import LoanDetail from "@/pages/loans/LoanDetail";
import LoanList from "@/pages/loans/LoanList";
import LoanPay from "@/pages/loans/LoanPay";
import SavingCreate from "@/pages/savings/SavingCreate";
import SavingDetail from "@/pages/savings/SavingDetail";
import SavingList from "@/pages/savings/SavingList";

function PageLoader() {
    return (
        <div className="flex justify-center items-center min-h-[50vh]">
            <div className="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-black" />
        </div>
    );
}

function ProtectedRoute() {
    const { loading } = useAuth();

    if (!getToken()) return <Navigate to="/login" replace />;
    if (loading) return <PageLoader />;
    return <Outlet />;
}

function GuestRoute() {
    const { loading } = useAuth();

    if (getToken()) return <Navigate to="/home" replace />;
    if (loading) return <PageLoader />;
    return <Outlet />;
}

export default function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    <Route element={<GuestRoute />}>
                        <Route element={<AuthLayout />}>
                            <Route path="/login" element={<Login />} />
                        </Route>
                    </Route>

                    <Route element={<ProtectedRoute />}>
                        <Route element={<AppLayout />}>
                            <Route path="/home" element={<Home />} />
                            <Route path="/settings" element={<Settings />} />
                        </Route>

                        <Route element={<MenuLayout />}>
                            <Route path="/choose" element={<Choose />} />
                            <Route path="/loans" element={<LoanList />} />
                            <Route path="/loans/create" element={<LoanCreate />} />
                            <Route path="/loans/:loanId" element={<LoanDetail />} />
                            <Route path="/loans/:loanId/pay" element={<LoanPay />} />
                            <Route path="/savings" element={<SavingList />} />
                            <Route path="/savings/create" element={<SavingCreate />} />
                            <Route path="/savings/:savingId" element={<SavingDetail />} />
                        </Route>
                    </Route>

                    <Route path="/" element={<Navigate to="/home" replace />} />
                    <Route path="*" element={<Navigate to="/home" replace />} />
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}

const root = document.getElementById("app");

if (root) {
    createRoot(root).render(
        <StrictMode>
            <App />
        </StrictMode>,
    );
}
