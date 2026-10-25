import { Outlet, useLocation } from "react-router-dom";
import MobileNav from "./MobileNav";
import Navbar from "./Navbar";

const BOTTOM_BAR_PATTERNS = [
    /^\/savings\/create$/,
    /^\/loans\/create$/,
    /^\/loans\/\d+\/pay$/,
];

export function AppLayout() {
    return (
        <div className="bg-gray-100 text-gray-900 overflow-x-hidden min-h-screen">
            <div className="hidden md:block">
                <Navbar />
            </div>

            <div className="flex w-full">
                <main className="flex-1 p-6 overflow-x-hidden">
                    <div className="max-w-xl mx-auto space-y-6 pb-24 md:pb-6">
                        <Outlet />
                    </div>
                </main>
            </div>

            <MobileNav />
        </div>
    );
}

export function MenuLayout() {
    const { pathname } = useLocation();
    const hasActionBottomBar = BOTTOM_BAR_PATTERNS.some((pattern) =>
        pattern.test(pathname),
    );

    return (
        <div className="bg-gray-100 text-gray-900 min-h-screen">
            <div className="hidden md:block">
                <Navbar />
            </div>

            <div className="flex w-full">
                <main className="flex-1 p-6">
                    <div className="max-w-xl mx-auto space-y-6 pb-24 md:pb-6">
                        <Outlet />
                    </div>
                </main>
            </div>

            {!hasActionBottomBar && <MobileNav />}
        </div>
    );
}

export function AuthLayout() {
    return (
        <div className="bg-gray-100 text-gray-900 overflow-x-hidden min-h-screen">
            <div className="flex w-full overflow-x-hidden">
                <main className="flex-1 p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
