import { Home, Plus, Settings } from "lucide-react";
import { Link } from "react-router-dom";

export default function MobileNav() {
    return (
        <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 shadow-lg flex justify-around py-2 z-50 md:hidden">
            <Link
                to="/home"
                className="flex-1 flex flex-col items-center text-gray-600 hover:text-black"
            >
                <Home className="w-6 h-6" />
                <span className="text-xs mt-1 text-center">Home</span>
            </Link>

            <Link to="/choose" className="flex-1 flex flex-col items-center text-gray-700">
                <div className="bg-black text-white w-12 h-12 rounded-full flex items-center justify-center shadow-md -mt-6">
                    <Plus className="w-6 h-6 text-white" />
                </div>
                <span className="text-xs mt-1 text-center">Tambah</span>
            </Link>

            <Link
                to="/settings"
                className="flex-1 flex flex-col items-center text-gray-600 hover:text-black"
            >
                <Settings className="w-6 h-6" />
                <span className="text-xs mt-1 text-center">Pengaturan</span>
            </Link>
        </nav>
    );
}
