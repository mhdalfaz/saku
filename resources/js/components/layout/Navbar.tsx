import { Link } from "react-router-dom";

export default function Navbar() {
    return (
        <header className="flex w-full items-center justify-between gap-4 border-b border-gray-300 bg-white px-6 py-3 shadow-sm">
            <div className="text-xl font-semibold">Saku</div>

            <nav className="flex shrink-0 items-center gap-4">
                <Link to="/home" className="text-sm text-gray-700 hover:text-black">
                    Home
                </Link>
                <Link to="/choose" className="text-sm text-gray-700 hover:text-black">
                    Buat baru
                </Link>
                <Link to="/settings" className="text-sm text-gray-700 hover:text-black">
                    Setting
                </Link>
            </nav>
        </header>
    );
}
