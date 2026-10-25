import { ArrowLeft } from "lucide-react";
import { useLocation, useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";

export default function BackButton() {
    const { pathname } = useLocation();
    const navigate = useNavigate();
    const segments = pathname.split("/").filter(Boolean);
    const parentPath =
        segments.length > 1 ? `/${segments.slice(0, -1).join("/")}` : "/";

    return (
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            className="shrink-0 md:hidden"
            aria-label="Kembali"
            onClick={() => navigate(parentPath)}
        >
            <ArrowLeft />
        </Button>
    );
}
